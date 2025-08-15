<?php

namespace wcf\command\user;

use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Updates the groups of users. Removes unnecessary groups and adds missing groups.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UpdateUserGroups
{
    public function __construct(
        private readonly User $user
    ) {}

    /**
     * @return list<int>
     */
    public function __invoke(): array
    {
        $groupIDs = $this->user->getGroupIDs();

        $fixGroupIDs = [];
        $removeGroupIDs = [];

        if (!\in_array(UserGroup::EVERYONE, $groupIDs)) {
            $fixGroupIDs[] = UserGroup::EVERYONE;
            $groupIDs[] = UserGroup::EVERYONE;
        }

        if ($this->user->pendingActivation()) {
            if (!\in_array(UserGroup::GUESTS, $groupIDs)) {
                $fixGroupIDs[] = UserGroup::GUESTS;
                $groupIDs[] = UserGroup::GUESTS;
            }

            if (\in_array(UserGroup::USERS, $groupIDs)) {
                $removeGroupIDs[] = UserGroup::USERS;
            }
        } else {
            if (!\in_array(UserGroup::USERS, $groupIDs)) {
                $fixGroupIDs[] = UserGroup::USERS;
                $groupIDs[] = UserGroup::USERS;
            }

            if (\in_array(UserGroup::GUESTS, $groupIDs)) {
                $removeGroupIDs[] = UserGroup::GUESTS;
            }
        }

        $this->addUserGroups($fixGroupIDs);
        $this->removeUserGroups($removeGroupIDs);

        UserStorageHandler::getInstance()->update($this->user->userID, 'groupIDs', \serialize($groupIDs));

        return $groupIDs;
    }

    /**
     * @param list<int> $groupIDs
     */
    private function addUserGroups(array $groupIDs): void
    {
        if ($groupIDs === []) {
            return;
        }

        (new UserEditor($this->user))->addToGroups($groupIDs, false, false);
    }

    /**
     * @param list<int> $groupIDs
     */
    private function removeUserGroups(array $groupIDs): void
    {
        if ($groupIDs === []) {
            return;
        }

        (new UserEditor($this->user))->removeFromGroups($groupIDs);
    }
}
