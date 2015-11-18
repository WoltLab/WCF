<?php
namespace wcf\system\menu\acp;
use wcf\system\cache\builder\ACPMenuCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\menu\TreeMenu;

/**
 * Builds the acp menu.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.acp
 * @category	Community Framework
 */
class ACPMenu extends TreeMenu {
	/**
	 * @var ACPMenuCacheBuilder
	 */
	protected $acpMenuCacheBuilder;
	
	/**
	 * ACPMenu constructor.
	 * 
	 * @param       ACPMenuCacheBuilder     $acpMenuCacheBuilder
	 * @param       EventHandler            $eventHandler
	 */
	public function __construct(ACPMenuCacheBuilder $acpMenuCacheBuilder, EventHandler $eventHandler) {
		$this->acpMenuCacheBuilder = $acpMenuCacheBuilder;
		
		parent::__construct($eventHandler);
	}
	
	/**
	 * @see	TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		if (PACKAGE_ID == 0) {
			return;
		}
		
		$this->menuItems = $this->acpMenuCacheBuilder->getData();
	}
}
