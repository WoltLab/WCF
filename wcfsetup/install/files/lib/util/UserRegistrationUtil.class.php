<?php

namespace wcf\util;

use wcf\event\user\UsernameValidating;
use wcf\system\event\EventHandler;

/**
 * Contains user registration related functions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class UserRegistrationUtil
{
    /**
     * Forbid creation of UserRegistrationUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }

    /**
     * Returns true if the given name is a valid username.
     */
    public static function isValidUsername(string $name): bool
    {
        if (!UserUtil::isValidUsername($name)) {
            return false;
        }

        $length = \mb_strlen($name);
        if ($length < REGISTER_USERNAME_MIN_LENGTH || $length > REGISTER_USERNAME_MAX_LENGTH) {
            return false;
        }

        if (!self::checkForbiddenUsernames($name)) {
            return false;
        }

        $event = new UsernameValidating($name);
        EventHandler::getInstance()->fire($event);
        if ($event->defaultPrevented()) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if the given e-mail is a valid address.
     */
    public static function isValidEmail(string $email): bool
    {
        return UserUtil::isValidEmail($email) && self::checkForbiddenEmails($email);
    }

    /**
     * Returns false if the given name is a forbidden username.
     */
    public static function checkForbiddenUsernames(string $name): bool
    {
        return StringUtil::executeWordFilter($name, REGISTER_FORBIDDEN_USERNAMES);
    }

    /**
     * Returns false if the given email is a forbidden email.
     */
    public static function checkForbiddenEmails(string $email): bool
    {
        return StringUtil::executeWordFilter(
            $email,
            REGISTER_FORBIDDEN_EMAILS
        ) && (!StringUtil::trim(REGISTER_ALLOWED_EMAILS) || !StringUtil::executeWordFilter(
            $email,
            REGISTER_ALLOWED_EMAILS
        ));
    }

    /**
     * Returns the `passwordrules` attribute value.
     *
     * @see         https://developer.apple.com/password-rules/
     */
    public static function getPasswordRulesAttributeValue(): string
    {
        return "minlength:8;";
    }

    /**
     * Generates a random activation code with the given length.
     * Warning: A length greater than 9 is out of integer range.
     */
    public static function getActivationCode(int $length = 9): int
    {
        return \random_int(10 ** ($length - 1), 10 ** $length - 1);
    }

    /**
     * Generates a random field name.
     *
     * @param string $unused
     */
    public static function getRandomFieldName($unused): string
    {
        $hash = StringUtil::getRandomID();

        return \substr($hash, 0, \random_int(8, 16));
    }
}
