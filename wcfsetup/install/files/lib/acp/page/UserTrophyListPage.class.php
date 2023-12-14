<?php

namespace wcf\acp\page;

use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\Trophy;
use wcf\data\user\trophy\UserTrophyList;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User trophy list page.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class UserTrophyListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.userTrophy.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TROPHY'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.trophy.canAwardTrophy'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'time';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $objectListClassName = UserTrophyList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['userTrophyID', 'trophyID', 'username', 'time'];

    /**
     * The filter value for the username search.
     * @var string
     */
    public $username = '';

    /**
     * The filter value for the trophy id.
     * @var int
     */
    public $trophyID = 0;

    /**
     * The trophy instance for the filter value.
     * @var Trophy
     */
    public $trophy;

    /**
     * @inheritdoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['username'])) {
            $this->username = StringUtil::trim($_REQUEST['username']);
        }
        if (isset($_REQUEST['trophyID'])) {
            $this->trophyID = \intval($_REQUEST['trophyID']);
            $this->trophy = new Trophy($this->trophyID);

            if (!$this->trophy->getObjectID()) {
                $this->trophyID = 0;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlJoins .= ' LEFT JOIN wcf' . WCF_N . '_user users ON (users.userID = user_trophy.userID)';
        if ($this->trophyID) {
            $this->objectList->getConditionBuilder()->add('user_trophy.trophyID = ?', [$this->trophyID]);
        }

        if ($this->username) {
            $this->objectList->getConditionBuilder()->add(
                'user_trophy.userID IN (
                    SELECT  userID
                    FROM    wcf' . WCF_N . '_user
                    WHERE   username LIKE ?
                )',
                ['%' . $this->username . '%']
            );
        }
    }

    #[\Override]
    protected function readObjects()
    {
        if ($this->sortField == 'username') {
            $this->sqlOrderBy = 'users.username ' . $this->sortOrder . ', user_trophy.trophyID ' . $this->sortOrder;
        }
        parent::readObjects();
    }

    /**
     * @inheritdoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'trophyID' => $this->trophyID,
            'username' => $this->username,
            'trophyCategories' => TrophyCategoryCache::getInstance()->getCategories(),
        ]);
    }
}
