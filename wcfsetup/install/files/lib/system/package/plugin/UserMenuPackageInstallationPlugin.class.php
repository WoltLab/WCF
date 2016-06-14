<?php
namespace wcf\system\package\plugin;
use wcf\data\user\menu\item\UserMenuItemEditor;

/**
 * Installs, updates and deletes user menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class UserMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = UserMenuItemEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tableName = 'user_menu_item';
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'usermenuitem';
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$result = parent::prepareImport($data);
		
		// class name
		if (!empty($data['elements']['classname'])) {
			$result['className'] = $data['elements']['classname'];
		}
		
		// FontAwesome icon name
		if (!empty($data['elements']['iconclassname']) && preg_match('~^fa\-[a-z\-]+$~', $data['elements']['iconclassname'])) {
			$result['iconClassName'] = $data['elements']['iconclassname'];
		}
		
		return $result;
	}
}
