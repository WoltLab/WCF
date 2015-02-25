<?php
namespace wcf\system\cache\builder;
use wcf\data\user\profile\menu\item\UserProfileMenuItemList;

/**
 * Caches the user profile menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserProfileMenuCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$itemList = new UserProfileMenuItemList();
		$itemList->sqlOrderBy = "user_profile_menu_item.showOrder ASC";
		$itemList->readObjects();
		
		return $itemList->getObjects();
	}
}
