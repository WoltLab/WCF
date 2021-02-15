<?php

namespace wcf\acp\page;

use wcf\data\email\log\entry\EmailLogEntryList;
use wcf\page\SortablePage;
use wcf\system\cache\runtime\UserRuntimeCache;

/**
 * Shows email logs.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Page
 *
 * @property    EmailLogEntryList $objectList
 */
class EmailLogListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.log.email';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canViewLog'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 100;

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
    public $validSortFields = ['entryID', 'time', 'status'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = EmailLogEntryList::class;

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $userIDs = \array_filter(\array_column($this->objectList->getObjects(), 'recipientID'));
        UserRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
    }
}
