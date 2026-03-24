<?php

namespace wcf\system\user\authentication;

use wcf\data\user\User;
use wcf\system\exception\UserInputException;

/**
 * Default user authentication implementation that uses the username to identify users.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DefaultUserAuthentication extends AbstractUserAuthentication
{
    /**
     * @return false
     * @deprecated 5.4 - This method always returns false, as the legacy automated login was removed.
     */
    public function supportsPersistentLogins()
    {
        return false;
    }

    /**
     * @param string $username
     * @param string $password
     * @return void
     * @deprecated 5.4 - This method is a noop, as user sessions are long-lived now.
     */
    public function storeAccessData(User $user, $username, $password)
    {
        // Does nothing
    }

    #[\Override]
    public function loginManually(
        string $username,
        #[\SensitiveParameter]
        string $password,
        string $userClassname = User::class
    ) {
        $user = $this->getUserByLogin($username);
        // @phpstan-ignore argument.type
        $userSession = (\get_class($user) == $userClassname ? $user : new $userClassname(null, null, $user));

        if ($userSession->userID == 0) {
            throw new UserInputException('username', 'notFound');
        }

        // check password
        if (!$userSession->checkPassword($password)) {
            throw new UserInputException('password', 'false');
        }

        return $userSession;
    }

    /**
     * @param bool $persistent
     * @param string $userClassname
     * @return void
     * @deprecated 5.4 - This method always returns null, as user sessions are long-lived now.
     */
    public function loginAutomatically($persistent = false, $userClassname = User::class) {}

    /**
     * Returns a user object by given login name.
     *
     * @param string $login
     * @return  User
     */
    protected function getUserByLogin($login)
    {
        return User::getUserByUsername($login);
    }

    /**
     * @param int $userID
     * @param string $password
     * @param string $userClassname
     * @return void
     * @deprecated 5.4 - This method always returns null, as user sessions are long-lived now.
     */
    protected function getUserAutomatically($userID, $password, $userClassname = User::class) {}

    /**
     * @param string $user
     * @param string $password
     * @return false
     * @deprecated 5.4 - This method always returns false, as user sessions are long-lived now.
     */
    protected function checkCookiePassword($user, $password)
    {
        return false;
    }
}
