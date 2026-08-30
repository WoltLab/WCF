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

        if ($userSession->isGuest()) {
            throw new UserInputException('username', 'notFound');
        }

        // check password
        if (!$userSession->checkPassword($password)) {
            throw new UserInputException('password', 'false');
        }

        return $userSession;
    }

    /**
     * Returns a user object by given login name.
     *
     * @return  User
     */
    protected function getUserByLogin(string $login)
    {
        return User::getUserByUsername($login);
    }
}
