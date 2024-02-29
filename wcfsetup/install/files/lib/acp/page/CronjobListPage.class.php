<?php

namespace wcf\acp\page;

use wcf\data\cronjob\I18nCronjobList;
use wcf\page\SortablePage;

/**
 * Shows information about configured cron jobs.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    I18nCronjobList $objectList
 */
class CronjobListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cronjob.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canManageCronjob'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'descriptionI18n';

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 100;

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'cronjobID',
        'nextExec',
        'descriptionI18n',
        'packageID',
    ];

    /**
     * @inheritDoc
     */
    public $objectListClassName = I18nCronjobList::class;

    /**
     * @inheritDoc
     */
    public function initObjectList()
    {
        parent::initObjectList();

        $this->sqlOrderBy = "cronjob." . $this->sortField . " " . $this->sortOrder;
    }
}
