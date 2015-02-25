<?php
namespace wcf\system\cache\builder;
use wcf\data\user\menu\item\UserMenuItem;
use wcf\system\WCF;

/**
 * Caches the user menu item tree.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserMenuCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$data = array();
		
		// get all option categories
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_option_category
			WHERE		parentCategoryName = ?
			ORDER BY	showOrder ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array('settings'));
		while ($row = $statement->fetchArray()) {
			if (!isset($data['wcf.user.menu.settings'])) {
				$data['wcf.user.menu.settings'] = array();
			}
			
			$categoryShortName = str_replace('settings.', '', $row['categoryName']);
			
			$data['wcf.user.menu.settings'][] = new UserMenuItem(null, array(
				'packageID' => $row['packageID'],
				'menuItem' => 'wcf.user.option.category.'.$row['categoryName'],
				'parentMenuItem' => 'wcf.user.menu.settings',
				'menuItemController' => 'wcf\form\SettingsForm',
				'menuItemLink' => ($categoryShortName != 'general' ? 'category='.$categoryShortName : ''),
				'permissions' => $row['permissions'],
				'options' => $row['options']
			));
		}
		
		// get all menu items
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_menu_item
			ORDER BY	showOrder ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if (!isset($data[$row['parentMenuItem']])) {
				$data[$row['parentMenuItem']] = array();
			}
			
			$data[$row['parentMenuItem']][] = new UserMenuItem(null, $row);
		}
		
		return $data;
	}
}
