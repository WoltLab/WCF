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
 * @package WoltLabSuite\Core\Util
 * @since   3.0
 */
final class CryptoUtil
{
    /**
     * Signs the given value with the signature secret.
     *
     * @param string $value
     * @throws  CryptoException
     */
    public static function getSignature($value): string
    {
        if (\mb_strlen(SIGNATURE_SECRET, '8bit') < 15) {
            throw new CryptoException('SIGNATURE_SECRET is too short, aborting.');
        }

        return \hash_hmac('sha256', $value, SIGNATURE_SECRET);
    }

    /**
     * Creates a signed (signature + encoded value) string.
     *
     * @param string $value
     */
    public static function createSignedString($value): string
    {
        return self::getSignature($value) . '-' . Base64::encode($value);
    }

    /**
     * Returns whether the given string is a proper signed string.
     * (i.e. consists of a valid signature + encoded value)
     *
     * @param string $string
     */
    public static function validateSignedString($string): bool
    {
        $parts = \explode('-', $string, 2);
        if (\count($parts) !== 2) {
            return false;
        }
        [$signature, $value] = $parts;

        try {
            $value = Base64::decode($value);
        } catch (\RangeException $e) {
            return false;
        }

        return \hash_equals($signature, self::getSignature($value));
    }

    /**
     * Returns the value of a signed string, after
     * validating whether it is properly signed.
     *
     * - Returns null if the string is not properly signed.
     *
     * @param string $string
     * @see     \wcf\util\CryptoUtil::validateSignedString()
     */
    public static function getValueFromSignedString($string): ?string
    {
        if (!self::validateSignedString($string)) {
            return null;
        }

        $parts = \explode('-', $string, 2);
        try {
            return Base64::decode($parts[1]);
        } catch (\RangeException $e) {
            throw new \LogicException('Unreachable', 0, $e);
        }
    }

    /**
     * @deprecated  Use \hash_equals() directly.
     */
    public static function secureCompare($hash1, $hash2)
    {
        $hash1 = (string)$hash1;
        $hash2 = (string)$hash2;

        return \hash_equals($hash1, $hash2);
    }

    /**
     * @deprecated  Use \random_bytes() directly.
     */
    public static function randomBytes($n)
    {
        return \random_bytes($n);
    }

    /**
     * @deprecated  Use \random_int() directly.
     */
    public static function randomInt($min, $max)
    {
        $range = $max - $min;
        if ($range == 0) {
            // not random
            throw new CryptoException("Cannot generate a secure random number, min and max are the same");
        }

        return \random_int($min, $max);
    }

    /**
     * Forbid creation of CryptoUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
