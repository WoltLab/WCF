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
        if (\mb_strlen($data, '8bit') !== VAPID::PUBLIC_KEY_LENGTH || $data[0] !== "\x04") {
            throw new \InvalidArgumentException('Invalid public key format.');
        }
        $data = \mb_substr($data, 1, null, '8bit');
        $dataLength = \mb_strlen($data, '8bit');

        [$x, $y] = \mb_str_split($data, \intdiv($dataLength, 2), '8bit');
        return [
            'x' => $x,
            'y' => $y,
        ];
    }

    /**
     * Serialize the public key.
     *
     * @param string $x encoded base64 x coordinate
     * @param string $y encoded base64 y coordinate
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
