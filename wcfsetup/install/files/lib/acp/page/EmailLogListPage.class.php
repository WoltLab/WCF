<?php

namespace wcf\acp\page;

use wcf\data\email\log\entry\EmailLogEntryList;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\WCF;

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
     * @var array
     */
    public $filter = [
        'username' => null,
        'status' => null,
        'email' => null,
    ];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $userIDs = \array_filter(\array_column($this->objectList->getObjects(), 'recipientID'));
        UserRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['filter']) && \is_array($_REQUEST['filter'])) {
            foreach ($_REQUEST['filter'] as $key => $value) {
                if (\array_key_exists($key, $this->filter)) {
                    $this->filter[$key] = $value;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        if (!empty($this->filter['username'])) {
            $this->objectList->getConditionBuilder()->add('recipientID = ?', [
                User::getUserByUsername($this->filter['username'])->userID,
            ]);
        }
        if (!empty($this->filter['status'])) {
            $this->objectList->getConditionBuilder()->add('status = ?', [$this->filter['status']]);
        }
        if (WCF::getSession()->getPermission('admin.user.canEditMailAddress')) {
            if (!empty($this->filter['email'])) {
                $this->objectList->getConditionBuilder()->add('recipient = ?', [$this->filter['email']]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'filter' => $this->filter,
            'filterParameter' => \http_build_query(['filter' => $this->filter], '', '&'),
        ]);
    }
}
