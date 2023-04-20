<?php

namespace wcf\util;

use Spoofchecker;
use wcf\system\event\EventHandler;
use wcf\system\user\event\UsernameValidating;

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

        switch (REGISTER_USERNAME_FORCE_ASCII) {
            case 0:
                break;
            case 1:
                if (!\preg_match('/^[\x20-\x7E]+$/', $name)) {
                    return false;
                }
                break;
            case 2:
                $spoofchecker = new \Spoofchecker();
                $checks = Spoofchecker::INVISIBLE;
                if (\defined(Spoofchecker::class . '::HIDDEN_OVERLAY')) {
                    // The constant will exist with PHP 8.3.
                    $checks |= Spoofchecker::HIDDEN_OVERLAY;
                } else {
                    // HIDDEN_OVERLAY == 256
                    $checks |= 256;
                }

                // ->setRestrictionLevel() requires ICU 58.
                if (\method_exists($spoofchecker, 'setRestrictionLevel')) {
                    // This method needs to be called first. ->setRestrictionLevel() will
                    // implicitly enable the check for the restriction level for which no
                    // constant exists. When calling ->setChecks() after ->setRestrictionLevel()
                    // the check will be implicitly disabled again.
                    $spoofchecker->setChecks($checks);

                    // https://unicode.org/reports/tr39/#Restriction_Level_Detection
                    $spoofchecker->setRestrictionLevel(Spoofchecker::HIGHLY_RESTRICTIVE);
                } else {
                    $spoofchecker->setChecks($checks | Spoofchecker::SINGLE_SCRIPT);
                }

                \assert(
                    $spoofchecker->isSuspicious("GREEK CAPITAL LETTER SIGMA: \u{03A3}"),
                    "The restriction level check was not correctly enabled."
                );

                if ($spoofchecker->isSuspicious($name)) {
                    return false;
                }
                break;
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
