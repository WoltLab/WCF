<?php

namespace wcf\system\cache\runtime;

use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;

/**
 * Runtime cache implementation for user profiles.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractRuntimeCache<UserProfile, UserProfileList>
 */
class UserProfileRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = UserProfileList::class;

    /**
     * Adds a user profile to the cache. This is an internal method that should
     * not be used on a regular basis.
     *
     * @return void
     */
    public function addUserProfile(UserProfile $profile)
    {
        $objectID = $profile->getObjectID();

        if (!isset($this->objects[$objectID])) {
            $this->objects[$objectID] = $profile;
        }
    }
}
