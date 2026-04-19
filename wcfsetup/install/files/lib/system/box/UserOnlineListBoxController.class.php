<?php

namespace wcf\system\box;

use wcf\data\user\online\UsersOnlineList;
use wcf\page\UsersOnlineListPage;
use wcf\system\event\EventHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Box controller for a list of registered users who are currently online.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectListBoxController<UsersOnlineList>
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
    public $defaultSortField = \USERS_ONLINE_DEFAULT_SORT_FIELD;

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = \USERS_ONLINE_DEFAULT_SORT_ORDER;

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

    #[\Override]
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(UsersOnlineListPage::class);
    }

    #[\Override]
    protected function getObjectList()
    {
        return new UsersOnlineList();
    }

    #[\Override]
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

    #[\Override]
    protected function getTemplate()
    {
        $templateName = 'boxUsersOnlineSidebar';
        if ($this->getBox()->position == 'footerBoxes') {
            $templateName = 'boxUsersOnline';
        }

        return WCF::getTPL()->render(
            'wcf',
            $templateName,
            ['usersOnlineList' => $this->objectList, '__showRecord' => $this->showRecord]
        );
    }

    #[\Override]
    public function hasContent()
    {
        if (!\MODULE_USERS_ONLINE || !WCF::getSession()->hasPermission('user.profile.canViewUsersOnlineList')) {
            return false;
        }

        // Call parent method, as it loads the statistics.
        parent::hasContent();

        return $this->objectList->stats['total'] > 0;
    }

    #[\Override]
    public function hasLink()
    {
        return true;
    }

    #[\Override]
    public function getTitle()
    {
        return $this->title;
    }
}
