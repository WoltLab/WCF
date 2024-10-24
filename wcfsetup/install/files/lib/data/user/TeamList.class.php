<?php

namespace wcf\data\user;

use wcf\data\user\group\Team;
use wcf\data\user\group\UserGroup;
use wcf\system\WCF;

/**
 * Represents a list of team user groups.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TeamList extends UserProfileList
{
    /**
     * teams included in the list
     * @var Team[]
     */
    protected $teams = [];

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_user_group user_group,
                        wcf1_user_to_group user_to_group
                WHERE   user_to_group.groupID = user_group.groupID
                    AND user_group.showOnTeamPage = 1";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        return $statement->fetchSingleColumn();
    }

    /**
     * @inheritDoc
     */
    public function readObjectIDs()
    {
        $this->objectIDs = [];
        $sql = "SELECT      user_to_group.userID AS objectID
                FROM        wcf1_user_group user_group,
                            wcf1_user_to_group user_to_group
                LEFT JOIN   wcf1_user user_table
                ON          user_table.userID = user_to_group.userID
                WHERE       user_to_group.groupID = user_group.groupID
                        AND user_group.showOnTeamPage = 1
                ORDER BY    user_group.priority DESC" . (!empty($this->sqlOrderBy) ? ", " . $this->sqlOrderBy : '');
        $statement = WCF::getDB()->prepare($sql, $this->sqlLimit, $this->sqlOffset);
        $statement->execute();
        $this->objectIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        $sql = "SELECT      user_to_group.*
                FROM        wcf1_user_group user_group,
                            wcf1_user_to_group user_to_group
                LEFT JOIN   wcf1_user user_table
                ON          user_table.userID = user_to_group.userID
                WHERE       user_to_group.groupID = user_group.groupID
                        AND user_group.showOnTeamPage = 1
                ORDER BY    user_group.priority DESC" . (!empty($this->sqlOrderBy) ? ", " . $this->sqlOrderBy : '');
        $statement = WCF::getDB()->prepare($sql, $this->sqlLimit, $this->sqlOffset);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if (!isset($this->teams[$row['groupID']])) {
                $userGroup = UserGroup::getGroupByID($row['groupID']);
                $this->teams[$row['groupID']] = new Team($userGroup);
            }

            $this->teams[$row['groupID']]->addMember($this->objects[$row['userID']]);
        }
    }

    /**
     * Returns the teams in the list.
     *
     * @return  Team[]
     */
    public function getTeams()
    {
        return $this->teams;
    }
}
