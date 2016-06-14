<?php
namespace wcf\acp\page;
use wcf\data\cronjob\CronjobList;
use wcf\page\SortablePage;

/**
 * Shows information about configured cron jobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	CronjobList	$objectList
 */
class CronjobListPage extends SortablePage {
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
	public $defaultSortField = 'cronjobID';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['cronjobID', 'nextExec', 'startMinute', 'startHour', 'startDom', 'startMonth', 'startDow'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = CronjobList::class;
	
	/**
	 * @inheritDoc
	 */
	public function initObjectList() {
		parent::initObjectList();
		
		$this->sqlOrderBy = "cronjob.".$this->sortField." ".$this->sortOrder;
	}
}
