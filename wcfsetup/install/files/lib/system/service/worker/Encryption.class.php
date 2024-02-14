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
     * Return the encrypted payload.
     * {@link https://www.rfc-editor.org/rfc/rfc8291}
     *
     * @param ServiceWorker $serviceWorker
     * @param string $payload
     * @return string the encrypted payload
     */
    public static function encrypt(ServiceWorker $serviceWorker, string $payload): string
    {
        // Section 3.1
        // user
        $userPublicKey = Base64Url::decode($serviceWorker->publicKey);
        $userAuthToken = Base64Url::decode($serviceWorker->authToken);
        ['x' => $x, 'y' => $y] = Util::unserializePublicKey($userPublicKey);
        $userJwk = Util::createJWK($x, $y);
        // application-server
        $newJwk = JWKFactory::createECKey(Encryption::CURVE_ALGORITHM);
        $newPublicKey = \hex2bin(Util::serializePublicKey($newJwk->get('x'), $newJwk->get('y')));
        \assert($newPublicKey, "Failed to serialize public key");
        $sharedSecret = Encryption::getSharedSecret($userJwk, $newJwk);

        // Section 3.3
        $ikm = \hash_hkdf(
            Encryption::HASH_ALGORITHM,
            $sharedSecret,
            32,
            "WebPush: info\0" . $userPublicKey . $newPublicKey,
            $userAuthToken
        );
        // Section 3.4
        $salt = \random_bytes(16);
        $cek = \hash_hkdf(
            Encryption::HASH_ALGORITHM,
            $ikm,
            16,
            "Content-Encoding: aes128gcm\0",
            $salt
        );
        $nonce = \hash_hkdf(
            Encryption::HASH_ALGORITHM,
            $ikm,
            12,
            "Content-Encoding: nonce\0",
            $salt
        );
        // Section 4
        $payload = Encryption::addPadding($payload);
        $encryptionContentCodingHeader = $salt
            . \pack('N*', 4096)
            . \pack('C*', Binary::safeStrlen($newPublicKey))
            . $newPublicKey;

        $tag = '';
        $encryptedText = \openssl_encrypt(
            $payload,
            'aes-128-gcm',
            $cek,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        return $encryptionContentCodingHeader . $encryptedText . $tag;
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

    private static function addPadding(string $payload): string
    {
        $length = Binary::safeStrlen($payload);
        $paddingLength = ServiceWorkerHandler::MAX_PAYLOAD_LENGTH - $length;

        return \str_pad($payload . "\2", $paddingLength + $length, "\0", STR_PAD_RIGHT);
    }
}
