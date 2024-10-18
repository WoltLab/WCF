<?php

namespace wcf\acp\page;

use wcf\data\cronjob\CronjobList;
use wcf\data\cronjob\I18nCronjobList;
use wcf\data\cronjob\log\CronjobLogList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows cronjob log information.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    CronjobLogList $objectList
 */
class CronjobLogListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.log.cronjob';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canManageCronjob'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 100;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'execTime';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['cronjobID', 'className', 'description', 'execTime', 'success'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = CronjobLogList::class;

    /**
     * @var int
     */
    public $cronjobID = 0;

    /**
     * @var int
     */
    public $success = -1;

    /**
     * @var CronjobList
     */
    public $availableCronjobs;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['cronjobID'])) {
            $this->cronjobID = \intval($_REQUEST['cronjobID']);
        }
        if (isset($_REQUEST['success'])) {
            $this->success = \intval($_REQUEST['success']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects = "cronjob.*";
        $this->objectList->sqlJoins = "
            LEFT JOIN   wcf1_cronjob cronjob
            ON          cronjob.cronjobID = cronjob_log.cronjobID";

        if ($this->cronjobID) {
            $this->objectList->getConditionBuilder()->add('cronjob_log.cronjobID = ?', [$this->cronjobID]);
        }
        if ($this->success != -1) {
            $this->objectList->getConditionBuilder()->add('cronjob_log.success = ?', [$this->success]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        $this->sqlOrderBy = (($this->sortField == 'className' || $this->sortField == 'description') ? 'cronjob.' : 'cronjob_log.') . $this->sortField . " " . $this->sortOrder;

        parent::readObjects();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->availableCronjobs = new I18nCronjobList();
        $this->availableCronjobs->sqlOrderBy = 'descriptionI18n';
        $this->availableCronjobs->readObjects();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'cronjobID' => $this->cronjobID,
            'success' => $this->success,
            'availableCronjobs' => $this->availableCronjobs,
        ]);
    }
}
