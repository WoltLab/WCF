<?php

namespace wcf\data\user\trophy;

use wcf\data\DatabaseObjectCollection;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends DatabaseObjectCollection<UserTrophy>
 */
final class UserTrophyCollection extends DatabaseObjectCollection
{
    /**
     * @var array<int, Trophy>
     */
    private array $trophies;

    private bool $userProfilesLoaded = false;

    public function getUserProfile(UserTrophy $trophy): UserProfile
    {
        $this->loadUserProfiles();

        return UserProfileRuntimeCache::getInstance()->getObject($trophy->userID);
    }

    public function getTrophy(UserTrophy $trophy): Trophy
    {
        $this->loadTrophies();

        return $this->trophies[$trophy->trophyID];
    }

    private function loadUserProfiles(): void
    {
        if ($this->userProfilesLoaded) {
            return;
        }

        $this->userProfilesLoaded = true;

        $userIDs = [];
        foreach ($this->getObjects() as $object) {
            if ($object->userID) {
                $userIDs[] = $object->userID;
            }
        }

        if ($userIDs !== []) {
            UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
        }
    }

    private function loadTrophies(): void
    {
        if (isset($this->trophies)) {
            return;
        }

        $this->trophies = [];

        $trophies = TrophyCache::getInstance()->getTrophiesByID(\array_unique(\array_map(
            static fn (UserTrophy $userTrophy) => $userTrophy->trophyID,
            $this->getObjects()
        )));

        foreach ($trophies as $trophy) {
            $this->trophies[$trophy->trophyID] = $trophy;
        }
    }
}
