<?php

namespace wcf\util;

use wcf\system\email\Mailbox;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Contains user-related functions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class UserUtil
{
    /**
     * Returns true if the given name is a valid username.
     */
    public static function isValidUsername(string $name): bool
    {
        // minimum length is 3 characters, maximum length is 100 characters
        if (\mb_strlen($name) < 3 || \mb_strlen($name) > 100) {
            return false;
        }

        // Check for invalid bytes:
        // (a) ASCII control characters (0x00 - 0x19) are unacceptable.
        // (b) The comma is unacceptable (used as a separator in lists).
        // (c) Invalid UTF-8 sequences are unacceptable.
        if (!\preg_match('/^[^\x00-\x19,]+$/u', $name)) {
            return false;
        }

        // check long words
        $words = \preg_split('!\s+!', $name, -1, \PREG_SPLIT_NO_EMPTY);
        foreach ($words as $word) {
            if (\mb_strlen($word) > 20) {
                return false;
            }
        }
        // username must not be a valid e-mail
        if (self::isValidEmail($name)) {
            // Accept usernames that are a valid email address, but do not
            // contain any dots. As the domain part is expected to contain
            // at least one dot, this allow the use of common leetspeak
            // usernames, without accepting actually valid email addresses.
            if (\str_contains($name, '.')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @deprecated 5.5 Check whether `User::getUserByUsername()->userID` is falsy.
     */
    public static function isAvailableUsername($name): bool
    {
        $sql = "SELECT  COUNT(username)
                FROM    wcf1_user
                WHERE   username = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$name]);

        return $statement->fetchSingleColumn() == 0;
    }

    /**
     * Returns true if the given e-mail is a valid address.
     *
     * @see Mailbox::filterAddress()
     */
    public static function isValidEmail(string $email): bool
    {
        if (\mb_strlen($email) > 191) {
            return false;
        }

        try {
            Mailbox::filterAddress($email);

            return true;
        } catch (\DomainException $e) {
            return false;
        }
    }

    /**
     * @deprecated 5.5 Check whether `User::getUserByEmail()->userID` is falsy.
     */
    public static function isAvailableEmail($email): bool
    {
        $sql = "SELECT  COUNT(email)
                FROM    wcf1_user
                WHERE   email = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$email]);

        return $statement->fetchSingleColumn() == 0;
    }

    /**
     * Returns the user agent of the client.
     */
    public static function getUserAgent(): string
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            if (!StringUtil::isUTF8($userAgent)) {
                $userAgent = \mb_convert_encoding($userAgent, 'UTF-8', 'ISO-8859-1');
            }

            return \mb_substr($userAgent, 0, 191);
        }

        return '';
    }

    /**
     * Returns true if the active user uses a mobile browser.
     * @see http://detectmobilebrowser.com
     */
    public static function usesMobileBrowser(): bool
    {
        return (new UserAgent(self::getUserAgent()))->isMobileBrowser();
    }

    /**
     * Returns the ipv6 address of the client.
     */
    public static function getIpAddress(): string
    {
        $REMOTE_ADDR = '::';
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        }

        $REMOTE_ADDR = self::convertIPv4To6($REMOTE_ADDR);

        return $REMOTE_ADDR;
    }

    /**
     * Converts given ipv4 to ipv6.
     */
    public static function convertIPv4To6(string $ip): string
    {
        // drop Window's scope id (confused PHP)
        $ip = \preg_replace('~%[^%]+$~', '', $ip);

        if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) !== false) {
            // given ip is already ipv6
            return $ip;
        }

        if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) === false) {
            // invalid ip given
            return '';
        }

        $ipArray = \array_pad(\explode('.', $ip), 4, 0);
        $part7 = \base_convert(($ipArray[0] * 256) + $ipArray[1], 10, 16);
        $part8 = \base_convert(($ipArray[2] * 256) + $ipArray[3], 10, 16);

        return '::ffff:' . $part7 . ':' . $part8;
    }

    /**
     * Converts IPv6 embedded IPv4 address into IPv4 or returns input if true IPv6.
     */
    public static function convertIPv6To4(string $ip): string
    {
        // validate if given IP is a proper IPv6 address
        if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) === false) {
            // validate if given IP is a proper IPv4 address
            if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) === false) {
                // ip address is invalid
                return '';
            }

            return $ip;
        }

        // check if ip is a masked IPv4 address
        if (\substr($ip, 0, 7) == '::ffff:') {
            $ip = \substr($ip, 7);
            if (\preg_match('~^([a-f0-9]{1,4}):([a-f0-9]{1,4})$~', $ip, $matches)) {
                $ip = [
                    \base_convert($matches[1], 16, 10),
                    \base_convert($matches[2], 16, 10),
                ];

                $ipParts = [];
                $tmp = $ip[0] % 256;
                $ipParts[] = ($ip[0] - $tmp) / 256;
                $ipParts[] = $tmp;
                $tmp = $ip[1] % 256;
                $ipParts[] = ($ip[1] - $tmp) / 256;
                $ipParts[] = $tmp;

                return \implode('.', $ipParts);
            } else {
                return $ip;
            }
        } else {
            // given ip is an IPv6 address and cannot be converted
            return $ip;
        }
    }

    /**
     * Returns the request uri of the active request.
     */
    public static function getRequestURI(): string
    {
        $REQUEST_URI = '';

        $appendQueryString = true;
        if (!empty($_SERVER['ORIG_PATH_INFO']) && \strpos($_SERVER['ORIG_PATH_INFO'], '.php') !== false) {
            $REQUEST_URI = $_SERVER['ORIG_PATH_INFO'];
        } elseif (!empty($_SERVER['ORIG_SCRIPT_NAME'])) {
            $REQUEST_URI = $_SERVER['ORIG_SCRIPT_NAME'];
        } elseif (!empty($_SERVER['SCRIPT_NAME']) && (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO']))) {
            $REQUEST_URI = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
            $REQUEST_URI = $_SERVER['REQUEST_URI'];
            $appendQueryString = false;
        } elseif (!empty($_SERVER['PHP_SELF'])) {
            $REQUEST_URI = $_SERVER['PHP_SELF'];
        } elseif (!empty($_SERVER['PATH_INFO'])) {
            $REQUEST_URI = $_SERVER['PATH_INFO'];
        }
        if ($appendQueryString && !empty($_SERVER['QUERY_STRING'])) {
            $REQUEST_URI .= '?' . $_SERVER['QUERY_STRING'];
        }

        // fix encoding
        if (!StringUtil::isUTF8($REQUEST_URI)) {
            $REQUEST_URI = \mb_convert_encoding($REQUEST_URI, 'UTF-8', 'ISO-8859-1');
        }

        return \mb_substr(FileUtil::unifyDirSeparator($REQUEST_URI), 0, 255);
    }

    /**
     * Creates a guest token for the given username.
     *
     * @since 6.1
     */
    public static function createGuestToken(string $username): string
    {
        return CryptoUtil::createSignedString(JSON::encode(
            [
                'username' => $username,
                'time' => TIME_NOW,
            ]
        ));
    }

    /**
     * Verifies the given guest token and returns the stored username if the token is valid,
     * otherwise returns null.
     *
     * @since 6.1
     */
    public static function verifyGuestToken(string $token): ?string
    {
        if ($token === '') {
            return null;
        }

        $json = CryptoUtil::getValueFromSignedString($token);
        if ($json === null) {
            return null;
        }

        try {
            $data = JSON::decode($json);
        } catch (SystemException $e) {
            return null;
        }

        if (!\is_array($data) || !isset($data['username']) || !isset($data['time'])) {
            return null;
        }

        if ($data['time'] < \TIME_NOW - 30) {
            return null;
        }

        return $data['username'];
    }

    /**
     * Forbid creation of UserUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
