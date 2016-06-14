<?php
namespace wcf\system\cache\builder;
use wcf\data\user\menu\item\UserMenuItem;
use wcf\form\SettingsForm;
use wcf\system\WCF;

/**
 * Caches the user menu item tree.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class UserMenuCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = [];
		
		// get all option categories
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_option_category
			WHERE		parentCategoryName = ?
			ORDER BY	showOrder ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['settings']);
		while ($row = $statement->fetchArray()) {
			if (!isset($data['wcf.user.menu.settings'])) {
				$data['wcf.user.menu.settings'] = [];
			}
			
			$categoryShortName = str_replace('settings.', '', $row['categoryName']);
			
			$data['wcf.user.menu.settings'][] = new UserMenuItem(null, [
				'packageID' => $row['packageID'],
				'menuItem' => 'wcf.user.option.category.'.$row['categoryName'],
				'parentMenuItem' => 'wcf.user.menu.settings',
				'menuItemController' => SettingsForm::class,
				'menuItemLink' => ($categoryShortName != 'general' ? 'category='.$categoryShortName : ''),
				'permissions' => $row['permissions'],
				'options' => $row['options']
			]);
		}
		
		// get all menu items
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_menu_item
			ORDER BY	showOrder ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if (!isset($data[$row['parentMenuItem']])) {
				$data[$row['parentMenuItem']] = [];
			}
			
			$data[$row['parentMenuItem']][] = new UserMenuItem(null, $row);
		}
		
		return $data;
	}
}
