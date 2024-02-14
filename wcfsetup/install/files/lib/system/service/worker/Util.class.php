<?php

namespace wcf\system\service\worker;

use Base64Url\Base64Url;
use Jose\Component\Core\JWK;
use ParagonIE\ConstantTime\Binary;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class Util
{
    /**
     * Unserialize the public key into its x and y components.
     *
     * @param string $data
     * @return array{x: string, y: string}
     */
    public static function unserializePublicKey(string $data): array
    {
        $data = \bin2hex($data);
        if (Binary::safeSubstr($data, 0, 2) !== '04') {
            throw new \InvalidArgumentException('Invalid public key format');
        }
        $data = Binary::safeSubstr($data, 2);
        $dataLength = Binary::safeStrlen($data);

        return [
            'x' => \hex2bin(Binary::safeSubstr($data, 0, $dataLength / 2)),
            'y' => \hex2bin(Binary::safeSubstr($data, $dataLength / 2)),
        ];
    }

    /**
     * Serialize the public key.
     *
     * @param string $x
     * @param string $y
     * @return string
     */
    public static function serializePublicKey(string $x, string $y): string
    {
        $hexString = '04';
        $hexString .= \str_pad(\bin2hex(Base64Url::decode($x)), 64, '0', STR_PAD_LEFT);
        return $hexString . \str_pad(\bin2hex(Base64Url::decode($y)), 64, '0', STR_PAD_LEFT);
    }

    /**
     * Creates a JWK with EC P-256 curve.
     *
     * @param string|null $x non-encode base64 x coordinate
     * @param string|null $y non-encoded base64 y coordinate
     * @param string|null $d base64 encoded private key
     *
     * @return JWK
     */
    public static function createJWK(?string $x = null, string $y = null, #[\SensitiveParameter] ?string $d = null): JWK
    {
        $values = [
            'kty' => 'EC',
            'crv' => Encryption::CURVE_ALGORITHM,
        ];
        if ($x !== null) {
            $values['x'] = Base64Url::encode($x);
        }
        if ($y !== null) {
            $values['y'] = Base64Url::encode($y);
        }
        if ($d !== null) {
            $values['d'] = $d;
        }

        return new JWK($values);
    }
}
