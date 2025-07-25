<?php

namespace wcf\page;

use wcf\data\user\group\UserGroupList;
use wcf\system\listView\user\TeamListView;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;

/**
 * Shows the team members list.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TeamPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.profile.canViewMembersList'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TEAM_PAGE'];

    protected array $teams = [];

    #[\Override]
    public function readData()
    {
        parent::readData();

        $this->loadTeams();

        if (\MODULE_MEMBERS_LIST) {
            PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'teams' => $this->teams,
        ]);
    }

    protected function loadTeams(): void
    {
        $groupList = new UserGroupList();
        $groupList->getConditionBuilder()->add('showOnTeamPage = ?', [1]);
        $groupList->sqlOrderBy = 'priority DESC';
        $groupList->readObjects();

        foreach ($groupList->getObjects() as $team) {
            $this->teams[] = [
                'team' => $team,
                'listView' => new TeamListView($team->groupID),
            ];
        }
    }
}
