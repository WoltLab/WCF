<?php
namespace wcf\system\package\plugin;
use wcf\system\WCF;

/**
 * Installs, updates and deletes user profile menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class UserProfileMenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = 'wcf\data\user\profile\menu\item\UserProfileMenuItemEditor';
	
	/**
	 * @inheritDoc
	 */
	public $tableName = 'user_profile_menu_item';
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'userprofilemenuitem';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		menuItem = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$this->installation->getPackageID()
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		// adjust show order
		$showOrder = (isset($data['elements']['showorder'])) ? $data['elements']['showorder'] : null;
		$showOrder = $this->getShowOrder($showOrder);
		
		// merge values and default values
		return [
			'menuItem' => $data['attributes']['name'],
			'options' => (isset($data['elements']['options'])) ? $data['elements']['options'] : '',
			'permissions' => (isset($data['elements']['permissions'])) ? $data['elements']['permissions'] : '',
			'showOrder' => $showOrder,
			'className' => $data['elements']['classname']
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	menuItem = ?
				AND packageID = ?";
		$parameters = [
			$data['menuItem'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
}
