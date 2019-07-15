<?php
namespace wcf\system\menu\acp;
use wcf\data\acp\menu\item\ACPMenuItem;
use wcf\system\cache\builder\ACPMenuCacheBuilder;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\TreeMenu;
use wcf\system\WCF;

/**
 * Builds the acp menu.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Menu\Acp
 */
class ACPMenu extends TreeMenu {
	/**
	 * list of acp menu items that are only visible for owners in enterprise mode
	 * @var	string[]
	 * @since	5.2
	 */
	protected $enterpriseBlacklist = [
		'wcf.acp.menu.link.systemCheck'
	];
	
	/**
	 * @inheritDoc
	 * @param	ACPMenuItem	$item
	 */
	protected function checkMenuItem(ITreeMenuItem $item) {
		if (!parent::checkMenuItem($item)) {
			return false;
		}
		
		if (ENABLE_ENTERPRISE_MODE && !WCF::getUser()->hasOwnerAccess() && in_array($item->menuItem, $this->enterpriseBlacklist)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadCache() {
		parent::loadCache();
		
		if (PACKAGE_ID == 0) {
			return;
		}
		
		$this->menuItems = ACPMenuCacheBuilder::getInstance()->getData();
	}
}
