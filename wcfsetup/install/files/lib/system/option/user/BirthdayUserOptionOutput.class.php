<?php

namespace wcf\system\option\user;

use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\data\user\UserProfile;

/**
 * User option output implementation for the output of a user's birthday.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BirthdayUserOptionOutput extends DateUserOptionOutput
{
    /**
     * @inheritDoc
     */
    public function getOutput(User $user, UserOption $option, $value)
    {
        $profile = new UserProfile($user);

        return $profile->getBirthday();
    }
}
