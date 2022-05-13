<?php

namespace wcf\system\box;

use wcf\data\user\online\UsersOnlineList;
use wcf\system\event\EventHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Box controller for a list of registered users who are currently online.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Box
 * @since   3.0
 *
 * @property    UsersOnlineList $objectList
 */
class UserOnlineListBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['footerBoxes', 'sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    protected $conditionDefinition = 'com.woltlab.wcf.box.userOnlineList.condition';

    /**
     * @inheritDoc
     */
    public $defaultSortField = USERS_ONLINE_DEFAULT_SORT_FIELD;

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = USERS_ONLINE_DEFAULT_SORT_ORDER;

    /**
     * enables the display of the user online record
     * @var bool
     */
    public $showRecord = true;

    /**
     * @inheritDoc
     */
    protected $sortFieldLanguageItemPrefix = 'wcf.user.usersOnline.sortField';

    /**
     * phrase that is used for the box title
     * @var string|null
     */
    public $title;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['username', 'lastActivityTime', 'requestURI'];

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('UsersOnlineList');
    }

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        return new UsersOnlineList();
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        EventHandler::getInstance()->fireAction($this, 'readObjects');

        if ($this->sortOrder && $this->sortField === 'lastActivityTime') {
            $alias = $this->objectList->getDatabaseTableAlias();
            $this->objectList->sqlOrderBy = "session.{$this->sortField} {$this->sortOrder}, "
                . ($alias ? $alias . "." : "") . "{$this->objectList->getDatabaseTableIndexName()} {$this->sortOrder}";
        }

        $this->objectList->readStats();
        if ($this->showRecord) {
            $this->objectList->checkRecord();
        }
        $this->objectList->getConditionBuilder()->add('session.userID IS NOT NULL');

        $this->objectList->readObjects();
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        $templateName = 'boxUsersOnlineSidebar';
        if ($this->getBox()->position == 'footerBoxes') {
            $templateName = 'boxUsersOnline';
        }

        return WCF::getTPL()->fetch(
            $templateName,
            'wcf',
            ['usersOnlineList' => $this->objectList, '__showRecord' => $this->showRecord],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function hasContent()
    {
        if (!MODULE_USERS_ONLINE || !WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList')) {
            return false;
        }

        // Call parent method, as it loads the statistics.
        parent::hasContent();

        return $this->objectList->stats['total'] > 0;
    }

    /**
     * @inheritDoc
     */
    public function hasLink()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->title;
    }
}
