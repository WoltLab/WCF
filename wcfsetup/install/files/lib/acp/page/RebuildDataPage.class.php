<?php
namespace wcf\acp\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Show the list of available rebuild data options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class RebuildDataPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.maintenance.rebuildData';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canRebuildData'];
	
	/**
	 * object types
	 * @var	array
	 */
	public $objectTypes = [];
	
	/**
	 * display a warning if InnoDB uses a slow configuration
	 * @var	boolean
	 */
	public $showInnoDBWarning = false;
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get object types
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.rebuildData');
		
		// sort object types
		uasort($this->objectTypes, function ($a, $b) {
			$niceValueA = ($a->nicevalue ?: 0);
			$niceValueB = ($b->nicevalue ?: 0);
			
			if ($niceValueA < $niceValueB) {
				return -1;
			}
			else if ($niceValueA > $niceValueB) {
				return 1;
			}
			
			return 0;
		});
		
		$sql = "SHOW VARIABLES LIKE 'innodb_flush_log_at_trx_commit'";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$row = $statement->fetchArray();
		if ($row && $row['Value'] == 1) {
			$this->showInnoDBWarning = true;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'objectTypes' => $this->objectTypes,
			'showInnoDBWarning' => $this->showInnoDBWarning
		]);
	}
}
