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
     * @return void
     * @deprecated 5.4 - This method is a noop, as user sessions are long-lived now.
     */
    public function storeAccessData(User $user, string $username, string $password)
    {
        // Does nothing
    }

    /**
     * @template T of User
     * @param class-string<T> $userClassname class name of user class
     * @return T
     */
    #[\Override]
    public function loginManually(
        string $username,
        #[\SensitiveParameter]
        string $password,
        string $userClassname = User::class
    ) {
        $user = $this->getUserByLogin($username);
        // @phpstan-ignore argument.type
        $userSession = (\get_class($user) === $userClassname ? $user : new $userClassname(null, null, $user));

        if ((int)$userSession->userID === 0) {
            throw new UserInputException('username', 'notFound');
        }

        // check password
        if (!$userSession->checkPassword($password)) {
            throw new UserInputException('password', 'false');
        }

        return $userSession;
    }

    /**
     * @return void
     * @deprecated 5.4 - This method always returns null, as user sessions are long-lived now.
     */
    public function loginAutomatically(bool $persistent = false, string $userClassname = User::class) {}

    /**
     * Returns a user object by given login name.
     *
     * @return  User
     */
    protected function getUserByLogin(string $login)
    {
        return User::getUserByUsername($login);
    }

    /**
     * @return void
     * @deprecated 5.4 - This method always returns null, as user sessions are long-lived now.
     */
    protected function getUserAutomatically(int $userID, string $password, string $userClassname = User::class) {}

    /**
     * @return false
     * @deprecated 5.4 - This method always returns false, as user sessions are long-lived now.
     */
    protected function checkCookiePassword(string $user, string $password)
    {
        return false;
    }
}
