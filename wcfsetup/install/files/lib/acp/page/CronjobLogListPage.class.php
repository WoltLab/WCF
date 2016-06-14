<?php
namespace wcf\acp\page;
use wcf\data\cronjob\log\CronjobLogList;
use wcf\page\SortablePage;

/**
 * Shows cronjob log information.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	CronjobLogList		$objectList
 */
class CronjobLogListPage extends SortablePage {
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
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "cronjob.*";
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_cronjob cronjob ON (cronjob.cronjobID = cronjob_log.cronjobID)";
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		$this->sqlOrderBy = (($this->sortField == 'className' || $this->sortField == 'description') ? 'cronjob.' : 'cronjob_log.').$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
}
