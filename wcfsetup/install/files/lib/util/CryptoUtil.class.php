<?php

namespace wcf\util;

use ParagonIE\ConstantTime\Base64;
use wcf\util\exception\CryptoException;

/**
 * Contains cryptographic helper functions.
 * Features:
 * - Creating secure signatures based on the Keyed-Hash Message Authentication Code algorithm
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
final class CryptoUtil
{
    /**
     * Signs the given value with the signature secret.
     *
     * @throws  CryptoException
     */
    public static function getSignature(string $value): string
    {
        if (\mb_strlen(SIGNATURE_SECRET, '8bit') < 15) {
            throw new CryptoException('SIGNATURE_SECRET is too short, aborting.');
        }

        return \hash_hmac('sha256', $value, SIGNATURE_SECRET);
    }

    /**
     * Creates a signed (signature + encoded value) string.
     */
    public static function createSignedString(string $value): string
    {
        return self::getSignature($value) . '-' . Base64::encode($value);
    }

    /**
     * @deprecated 6.0 Check if getValueFromSignedString() is !== null.
     */
    public static function validateSignedString(string $string): bool
    {
        return self::getValueFromSignedString($string) !== null;
    }

    /**
     * Extracts the value from a string created with `createSignedString()`
     * after verifying the signature. If the signature is not valid, `null`
     * is returned.
     *
     * Note: The return value MUST be checked with a type-safe `!== null`
     * operation to not confuse a valid, but falsy, value such as `"0"`
     * with an invalid value (`null`).
     */
    public static function getValueFromSignedString(string $string): ?string
    {
        $parts = \explode('-', $string, 2);
        if (\count($parts) !== 2) {
            return null;
        }
        [$signature, $value] = $parts;

        try {
            $value = Base64::decode($value);
        } catch (\RangeException) {
            return null;
        }

        if (!\hash_equals($signature, self::getSignature($value))) {
            return null;
        }

        return $value;
    }

    /**
     * Forbid creation of CryptoUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
