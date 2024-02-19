<?php

namespace wcf\system\service\worker;

use Base64Url\Base64Url;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\ECKey;
use Jose\Component\KeyManagement\JWKFactory;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64UrlSafe;
use wcf\data\service\worker\ServiceWorker;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class Encryption
{
    public const CURVE_ALGORITHM = 'P-256';
    public const HASH_ALGORITHM = 'sha256';

    /**
     * Return the encrypted payload and necessary headers to send a push message to the push-service.
     * {@link https://www.rfc-editor.org/rfc/rfc8291}
     *
     * @param ServiceWorker $serviceWorker
     * @param string $payload
     * @param array $headers to this array the encryption headers will be added
     * @return string the encrypted payload
     */
    public static function encrypt(
        ServiceWorker $serviceWorker,
        #[\SensitiveParameter]
        string $payload,
        array &$headers
    ): string {
        // Section 3.1
        // user
        $userPublicKey = Base64Url::decode($serviceWorker->publicKey);
        ['x' => $x, 'y' => $y] = Util::unserializePublicKey($userPublicKey);
        $userJwk = Util::createJWK($x, $y);
        // application-server
        $newJwk = JWKFactory::createECKey(Encryption::CURVE_ALGORITHM);
        $newPublicKey = Util::serializePublicKey($newJwk->get('x'), $newJwk->get('y'));
        \assert($newPublicKey, "Failed to serialize public key");
        $sharedSecret = Encryption::getSharedSecret($userJwk, $newJwk);

        // Section 3.3
        $ikm = Encryption::getIKM($serviceWorker, $sharedSecret, $userPublicKey, $newPublicKey);
        // Section 3.4
        $salt = \random_bytes(16);
        $content = Encryption::createContext($userPublicKey, $newPublicKey, $serviceWorker->contentEncoding);

        $cek = \hash_hkdf(
            Encryption::HASH_ALGORITHM,
            $ikm,
            16,
            Encryption::createInfo($serviceWorker->contentEncoding, $content, $serviceWorker->contentEncoding),
            $salt
        );
        $nonce = \hash_hkdf(
            Encryption::HASH_ALGORITHM,
            $ikm,
            12,
            Encryption::createInfo('nonce', $content, $serviceWorker->contentEncoding),
            $salt
        );
        // Section 4
        $payload = Encryption::addPadding($payload, $serviceWorker->contentEncoding);

        $tag = '';
        $encryptedText = \openssl_encrypt(
            $payload,
            'aes-128-gcm',
            $cek,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($serviceWorker->contentEncoding === ServiceWorker::CONTENT_ENCODING_AESGCM) {
            $headers['Encryption'] = 'salt=' . Base64Url::encode($salt);
            $headers['Crypto-Key'] = 'dh=' . Base64Url::encode($newPublicKey);
        }
        $record = $encryptedText . $tag;

        return Encryption::getEncryptionContentCodingHeader(
            $serviceWorker->contentEncoding,
            \mb_strlen($record, '8bit'),
            $salt,
            $newPublicKey
        ) . $record;
    }

    private static function getSharedSecret(JWK $publicKey, #[\SensitiveParameter] JWK $privateKey): string
    {
        $result = \openssl_pkey_derive(
            ECKey::convertPublicKeyToPEM($publicKey),
            ECKey::convertPrivateKeyToPEM($privateKey),
            256
        );
        \assert($result);
        return \str_pad($result, 32, "\x00", STR_PAD_LEFT);
    }

    private static function addPadding(#[\SensitiveParameter] string $payload, string $contentEncoding): string
    {
        $length = \mb_strlen($payload, '8bit');
        $paddingLength = ServiceWorkerHandler::MAX_PAYLOAD_LENGTH - $length;

        if ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            return \str_pad($payload . "\x02", $paddingLength + $length, "\x00", STR_PAD_RIGHT);
        } elseif ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AESGCM) {
            return \pack('n*', $paddingLength) . \str_pad($payload, $paddingLength + $length, "\x00", STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('Unknown content encoding "' . $contentEncoding . '"');
        }
    }

    private static function createInfo(string $type, ?string $context, string $contentEncoding)
    {
        if ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AESGCM) {
            \assert($context !== null);
            \assert(\mb_strlen($context, '8bit') === 135);

            return 'Content-Encoding: ' . $type . "\x00" . Encryption::CURVE_ALGORITHM . $context;
        } elseif ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            return 'Content-Encoding: ' . $type . "\x00";
        } else {
            throw new \RuntimeException('Unknown content encoding "' . $contentEncoding . '"');
        }
    }

    private static function getEncryptionContentCodingHeader(
        string $contentEncoding,
        int $length,
        string $salt,
        string $publicKey
    ): string {
        if ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            /** {@link https://datatracker.ietf.org/doc/html/rfc8188#section-2.1} */
            return $salt
                . \pack('N*', $length)
                . \pack('C*', \mb_strlen($publicKey, '8bit'))
                . $publicKey;
        } elseif ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AESGCM) {
            return "";
        } else {
            throw new \RuntimeException('Unknown content encoding "' . $contentEncoding . '"');
        }
    }

    /**
     * {@link https://datatracker.ietf.org/doc/html/draft-ietf-httpbis-encryption-encoding-00#section-4.2}
     */
    private static function createContext(
        string $clientPublicKey,
        string $serverPublicKey,
        string $contentEncoding
    ): ?string {
        if ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            return null;
        }
        \assert(\mb_strlen($clientPublicKey, '8bit') === VAPID::PUBLIC_KEY_LENGTH);

        $len = \pack('n', 65);

        return "\x00" . $len . $clientPublicKey . $len . $serverPublicKey;
    }

    private static function getIKM(
        ServiceWorker $serviceWorker,
        #[\SensitiveParameter] string $sharedSecret,
        string $userPublicKey,
        string $newPublicKey
    ): string {
        if ($serviceWorker->contentEncoding === ServiceWorker::CONTENT_ENCODING_AESGCM) {
            $info = "Content-Encoding: auth\x00";
        } elseif ($serviceWorker->contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            $info = "WebPush: info\x00" . $userPublicKey . $newPublicKey;
        } else {
            throw new \RuntimeException('Unknown content encoding "' . $serviceWorker->contentEncoding . '"');
        }

        return \hash_hkdf(
            Encryption::HASH_ALGORITHM,
            $sharedSecret,
            32,
            $info,
            Base64Url::decode($serviceWorker->authToken)
        );
    }
}
