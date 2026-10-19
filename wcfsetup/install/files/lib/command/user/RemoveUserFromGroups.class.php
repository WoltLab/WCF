<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;

/**
 * Removes a user from the given user groups.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class RemoveUserFromGroups
{
    /**
     * @param list<int> $groupIDs
     */
    public function __construct(
        private readonly User $user,
        private readonly array $groupIDs,
        private readonly bool $ignoreUserGroupAssignments = false,
    ) {}

    public function __invoke(): void
    {
        $currentGroupIDs = $this->user->getGroupIDs(true);
        $groupIDs = \array_values(\array_diff($currentGroupIDs, $this->groupIDs));

        if (\count($groupIDs) === \count($currentGroupIDs)) {
            return;
        }

        new UpdateUser(
            UserBuilder::forUpdate($this->user)->setGroupIDs($groupIDs),
            $this->ignoreUserGroupAssignments
        )();
    }
}
