<?php

namespace wcf\system\user;

use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Wrapper for the profile of the active user to be used as a core object.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   UserProfile
 */
class UserProfileHandler extends SingletonFactory
{
    /**
     * user profile object
     * @var UserProfile
     */
    protected $userProfile;

    #[\Override]
    protected function init()
    {
        $this->userProfile = new UserProfile(WCF::getUser());
        $this->userProfile->setSessionLastActivityTime(\TIME_NOW);
    }

    /**
     * Delegates method calls to the user profile object.
     *
     * @param mixed[] $arguments
     * @return  mixed
     */
    public function __call(string $name, array $arguments)
    {
        return \call_user_func_array([$this->userProfile, $name], $arguments);
    }

    /**
     * Delegates property accesses to user profile object.
     *
     * @return  mixed
     */
    public function __get(string $name)
    {
        return $this->userProfile->{$name};
    }

    /**
     * Reloads the user profile object with data directly from the database.
     *
     * @return void
     */
    public function reloadUserProfile()
    {
        $this->userProfile = new UserProfile(new User($this->userID));
        $this->userProfile->setSessionLastActivityTime(\TIME_NOW);
    }

    /**
     * Returns the user profile object.
     *
     * @return      UserProfile
     */
    public function getUserProfile()
    {
        return $this->userProfile;
    }
}
