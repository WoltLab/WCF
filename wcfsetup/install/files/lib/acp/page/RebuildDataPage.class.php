<?php
namespace wcf\acp\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Show the list of available rebuild data options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class RebuildDataPage extends AbstractPage {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.maintenance.rebuildData';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canRebuildData');
	
	/**
	 * object types
	 * @var array
	 */
	public $objectTypes = array();
	
	/**
	 * @see	wcf\page\IPage::readData()
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
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'objectTypes' => $this->objectTypes
		));
	}
}
