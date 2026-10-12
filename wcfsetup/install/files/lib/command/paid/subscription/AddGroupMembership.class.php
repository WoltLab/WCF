<?php

namespace wcf\command\paid\subscription;

use wcf\command\user\AddUserToGroups;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;

/**
 * Adds user groups to a user based on the paid subscription's associated group IDs.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class AddGroupMembership
{
    public function __construct(
        private readonly PaidSubscription $subscription,
        private readonly User $user,
    ) {}

    public function __invoke(): void
    {
        $groupIDs = $this->getGroupIDs($this->subscription);

        if ($groupIDs === []) {
            return;
        }

        (new AddUserToGroups($this->user, $groupIDs))();
    }

    /**
     * @return list<int>
     */
    private function getGroupIDs(PaidSubscription $subscription): array
    {
        $groupIDs = [];

        foreach (\explode(',', $subscription->groupIDs) as $groupID) {
            $groupID = (int)$groupID;
            if (UserGroup::getGroupByID($groupID) !== null) {
                $groupIDs[] = $groupID;
            }
        }

        return $groupIDs;
    }
}
