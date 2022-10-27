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
     * Returns whether the given string is a proper signed string.
     * (i.e. consists of a valid signature + encoded value)
     */
    public static function validateSignedString(string $string): bool
    {
        return self::getValueFromSignedString($string) !== null;
    }

    /**
     * Returns the value of a signed string, after
     * validating whether it is properly signed.
     *
     * - Returns null if the string is not properly signed.
     *
     * @see     \wcf\util\CryptoUtil::validateSignedString()
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
