<?php

namespace wcf\system\listView\user;

use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfileList;

/**
 * List view for the team list.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class TeamListView extends MembersListView
{
    public function __construct(private readonly int $groupID)
    {
        parent::__construct();

        $this->setAllowFiltering(false);
        $this->setAllowSorting(false);
    }

    #[\Override]
    protected function createObjectList(): UserProfileList
    {
        $list = new UserProfileList();
        $list->getConditionBuilder()->add(
            'user_table.userID IN (SELECT userID FROM wcf1_user_to_group WHERE groupID = ?)',
            [$this->groupID]
        );

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        if (!parent::isAccessible()) {
            return false;
        }

        if (!\MODULE_TEAM_PAGE) {
            return false;
        }

        if (UserGroup::getGroupByID($this->groupID) === null || !UserGroup::getGroupByID($this->groupID)->showOnTeamPage) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'groupID' => $this->groupID,
        ];
    }
}
