<?php

namespace wcf\system\user\authentication;

use wcf\data\user\User;

/**
 * Every user authentication has to implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IUserAuthentication
{
    /**
     * Returns an unique instance of the authentication class
     *
     * @return  IUserAuthentication
     */
    public static function getInstance();

    /**
     * Does a manual user login or `null` if login was unsuccessful.
     *
     * @param class-string<T of User> $userClassname class name of user class
     * @return ?T
     */
    public function loginManually(string $username, string $password, string $userClassname = User::class);
}
