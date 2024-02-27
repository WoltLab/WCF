<?php

namespace wcf\page;

use wcf\data\page\PageCache;
use wcf\data\user\online\UserOnline;
use wcf\data\user\online\UsersOnlineList;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows page which lists all users who are online.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    UsersOnlineList $objectList
 */
class UsersOnlineListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.profile.canViewUsersOnlineList'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 100;

    /**
     * @inheritDoc
     */
    public $defaultSortField = USERS_ONLINE_DEFAULT_SORT_FIELD;

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = USERS_ONLINE_DEFAULT_SORT_ORDER;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['username', 'lastActivityTime', 'requestURI'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = UsersOnlineList::class;

    /**
     * page locations
     * @var array
     */
    public $locations = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (WCF::getSession()->getPermission('admin.user.canViewIpAddress')) {
            $this->validSortFields[] = 'ipAddress';
            $this->validSortFields[] = 'userAgent';
        }

        if (!empty($_POST)) {
            HeaderUtil::redirect(LinkHandler::getInstance()->getLink(
                'UsersOnlineList',
                [],
                'sortField=' . $this->sortField . '&sortOrder=' . $this->sortOrder
            ));

            exit;
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();
        $this->objectList->readStats();
        $this->objectList->checkRecord();

        if (!USERS_ONLINE_SHOW_ROBOTS) {
            $this->objectList->getConditionBuilder()->add('session.spiderIdentifier IS NULL');
        }
        if (!USERS_ONLINE_SHOW_GUESTS) {
            if (USERS_ONLINE_SHOW_ROBOTS) {
                $this->objectList->getConditionBuilder()->add('(session.userID IS NOT NULL OR session.spiderIdentifier IS NOT NULL)');
            } else {
                $this->objectList->getConditionBuilder()->add('session.userID IS NOT NULL');
            }
        }

        $this->objectList->sqlSelects .= ", CASE WHEN session.userID IS NULL THEN 1 ELSE 0 END AS userIsGuest";
        $this->objectList->sqlSelects .= ", CASE WHEN session.spiderIdentifier IS NOT NULL THEN 1 ELSE 0 END AS userIsRobot";
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // add breadcrumbs
        if (MODULE_MEMBERS_LIST) {
            PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
        }

        // cache all necessary data for showing locations
        foreach ($this->objectList as $userOnline) {
            $page = PageCache::getInstance()->getPage($userOnline->pageID);
            if ($page !== null && $page->getHandler() !== null && $page->getHandler() instanceof IOnlineLocationPageHandler) {
                /** @noinspection PhpUndefinedMethodInspection */
                $page->getHandler()->prepareOnlineLocation($page, $userOnline);
            }
        }

        // set locations
        /** @var UserOnline $userOnline */
        foreach ($this->objectList as $userOnline) {
            $userOnline->setLocation();
        }
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        if ($this->sqlOrderBy) {
            $this->sqlOrderBy = ($this->sortField == 'lastActivityTime' ? 'session.' : '') . $this->sqlOrderBy;
        }

        $originalSqlOrderBy = $this->sqlOrderBy;
        // sort in this order: users -> guests -> robots
        $this->sqlOrderBy = "userIsGuest ASC, userIsRobot DESC, " . $this->sqlOrderBy;

        parent::readObjects();

        // restore original order
        $this->sqlOrderBy = $originalSqlOrderBy;
    }
}
