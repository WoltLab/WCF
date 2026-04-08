<?php

namespace wcf\system\user\authentication;

use wcf\data\user\User;

/**
 * User authentication implementation that uses the e-mail address to identify users.
 *
 * @author  Markus Bartz
 * @copyright   2011 Markus Bartz
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class EmailUserAuthentication extends DefaultUserAuthentication
{
    #[\Override]
    protected function getUserByLogin(string $login)
    {
        return User::getUserByEmail($login);
    }
}
