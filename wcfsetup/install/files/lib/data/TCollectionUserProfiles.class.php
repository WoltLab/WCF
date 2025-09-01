<?php

namespace wcf\data;

use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Trait for dbo collections with user profiles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
trait TCollectionUserProfiles
{
    private bool $userProfilesLoaded = false;

    public function getUserProfile(
        DatabaseObject $object,
        string $userIdProperty = 'userID',
        string $usernameProperty = 'username'
    ): UserProfile {
        $this->loadUserProfiles($userIdProperty);

        if ($object->{$userIdProperty}) {
            return UserProfileRuntimeCache::getInstance()->getObject($object->{$userIdProperty});
        } else {
            return UserProfile::getGuestUserProfile($object->{$usernameProperty});
        }
    }

    private function loadUserProfiles(string $userIdProperty): void
    {
        if ($this->userProfilesLoaded) {
            return;
        }

        \assert($this instanceof DatabaseObjectCollection);

        $this->userProfilesLoaded = true;

        $userIDs = [];
        foreach ($this->getObjects() as $object) {
            if ($object->{$userIdProperty}) {
                $userIDs[] = $object->{$userIdProperty};
            }
        }

        if ($userIDs !== []) {
            UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
        }
    }
}
