<?php

namespace wcf\system\service\worker;

use Base64Url\Base64Url;
use Jose\Component\Core\JWK;
use Jose\Component\Core\Util\ECKey;
use Jose\Component\KeyManagement\JWKFactory;
use ParagonIE\ConstantTime\Binary;
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
    public static function encrypt(ServiceWorker $serviceWorker, string $payload, array &$headers): string
    {
        // Section 3.1
        // user
        $userPublicKey = Base64Url::decode($serviceWorker->publicKey);
        ['x' => $x, 'y' => $y] = Util::unserializePublicKey($userPublicKey);
        $userJwk = Util::createJWK($x, $y);
        // application-server
        $newJwk = JWKFactory::createECKey(Encryption::CURVE_ALGORITHM);
        $newPublicKey = \hex2bin(Util::serializePublicKey($newJwk->get('x'), $newJwk->get('y')));
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

        return Encryption::getEncryptionContentCodingHeader(
            $serviceWorker->contentEncoding,
            $salt,
            $newPublicKey
        ) . $encryptedText . $tag;
    }

    private static function getSharedSecret(JWK $publicKey, #[\SensitiveParameter] JWK $privateKey): string
    {
        $result = \openssl_pkey_derive(
            ECKey::convertPublicKeyToPEM($publicKey),
            ECKey::convertPrivateKeyToPEM($privateKey),
            256
        );
        \assert($result);
        return \str_pad($result, 32, "\0", STR_PAD_LEFT);
    }

    private static function addPadding(string $payload, string $contentEncoding): string
    {
        $length = Binary::safeStrlen($payload);
        $paddingLength = ServiceWorkerHandler::MAX_PAYLOAD_LENGTH - $length;

        if ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            return \str_pad($payload . "\2", $paddingLength + $length, "\0", STR_PAD_RIGHT);
        } elseif ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AESGCM) {
            return \pack('n*', $paddingLength) . \str_pad($payload, $paddingLength + $length, "\0", STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('Unknown content encoding "' . $contentEncoding . '"');
        }
    }

    private static function createInfo(string $type, ?string $context, string $contentEncoding)
    {
        if ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AESGCM) {
            \assert($context !== null);
            \assert(Binary::safeStrlen($context) === 135);

            return 'Content-Encoding: ' . $type . "\0" . Encryption::CURVE_ALGORITHM . $context;
        } elseif ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            return 'Content-Encoding: ' . $type . "\0";
        } else {
            throw new \RuntimeException('Unknown content encoding "' . $contentEncoding . '"');
        }
    }

    private static function getEncryptionContentCodingHeader(
        string $contentEncoding,
        string $salt,
        string $publicKey
    ): string {
        if ($contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            return $salt
                . \pack('N*', 4096)
                . \pack('C*', Binary::safeStrlen($publicKey))
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
        \assert(Binary::safeStrlen($clientPublicKey) === 65);

        $len = "\0A"; // 65 as Uint16BE

        return "\0" . $len . $clientPublicKey . $len . $serverPublicKey;
    }

    private static function getIKM(
        ServiceWorker $serviceWorker,
        #[\SensitiveParameter] string $sharedSecret,
        string $userPublicKey,
        string $newPublicKey
    ): string {
        if ($serviceWorker->contentEncoding === ServiceWorker::CONTENT_ENCODING_AESGCM) {
            $info = "Content-Encoding: auth\0";
        } elseif ($serviceWorker->contentEncoding === ServiceWorker::CONTENT_ENCODING_AES128GCM) {
            $info = "WebPush: info\0" . $userPublicKey . $newPublicKey;
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
