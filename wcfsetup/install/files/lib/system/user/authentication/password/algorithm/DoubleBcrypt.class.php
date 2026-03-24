<?php

namespace wcf\system\user\authentication\password\algorithm;

use wcf\system\Regex;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of "double salted" BCrypt as used in WoltLab Suite < 5.4.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
final class DoubleBcrypt implements IPasswordAlgorithm
{
    private static string $blowfishCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';

    /**
     * blowfish cost factor
     * @var string
     */
    private const BCRYPT_COST = '08';

    /**
     * blowfish encryption type
     * @var string
     */
    private const BCRYPT_TYPE = '2a';

    #[\Override]
    public function verify(
        #[\SensitiveParameter]
        string $password,
        string $hash
    ): bool {
        return \hash_equals($hash, self::getDoubleSaltedHash($password, $hash));
    }

    #[\Override]
    public function hash(
        #[\SensitiveParameter]
        string $password
    ): string {
        return self::getDoubleSaltedHash($password);
    }

    #[\Override]
    public function needsRehash(string $hash): bool
    {
        return self::isDifferentBlowfish($hash);
    }

    /**
     * Returns whether the given hash looks like a legacy DoubleBcrypt hash.
     */
    public static function isLegacyDoubleBcrypt(string $hash): bool
    {
        return Regex::compile('^\$2[afxy]\$')->match($hash) ? true : false;
    }

    /**
     * Returns a double salted bcrypt hash.
     */
    private static function getDoubleSaltedHash(
        #[\SensitiveParameter]
        string $password,
        ?string $salt = null
    ): string {
        if ($salt === null) {
            $salt = self::getRandomSalt();
        }

        return self::getSaltedHash(self::getSaltedHash($password, $salt), $salt);
    }

    /**
     * Returns a simple salted bcrypt hash.
     */
    private static function getSaltedHash(
        #[\SensitiveParameter]
        string $password,
        ?string $salt = null
    ): string {
        if ($salt === null) {
            $salt = self::getRandomSalt();
        }

        return \crypt($password, $salt);
    }

    /**
     * Returns a random blowfish-compatible salt.
     */
    private static function getRandomSalt(): string
    {
        $salt = '';

        for ($i = 0, $maxIndex = (\mb_strlen(self::$blowfishCharacters, '8bit') - 1); $i < 22; $i++) {
            $salt .= self::$blowfishCharacters[\random_int(0, $maxIndex)];
        }

        return self::getSalt($salt);
    }

    /**
     * Returns a blowfish salt, e.g. $2a$07$usesomesillystringforsalt$
     */
    private static function getSalt(string $salt): string
    {
        $salt = \mb_substr($salt, 0, 22, '8bit');

        return '$' . self::BCRYPT_TYPE . '$' . self::BCRYPT_COST . '$' . $salt;
    }

    /**
     * Returns true if given bcrypt hash uses a different cost factor and should be re-computed.
     */
    private static function isDifferentBlowfish(string $hash): bool
    {
        $currentCost = \intval(self::BCRYPT_COST);
        $hashCost = \intval(\mb_substr($hash, 4, 2, '8bit'));

        if ($currentCost != $hashCost) {
            return true;
        }

        return false;
    }
}
