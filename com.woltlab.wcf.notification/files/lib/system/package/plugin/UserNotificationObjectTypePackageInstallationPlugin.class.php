<?php
namespace wcf\system\package\plugin;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes user notification object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class UserNotificationObjectTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\user\notification\object\type\UserNotificationObjectTypeEditor';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'user_notification_object_type';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'objecttype';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND objectType = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$this->installation->getPackageID(),
				$item['elements']['name']
			));
		}
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		return array(
			'objectType' => $data['elements']['name'],
			'className' => $data['elements']['classname'],
			'permissions' => (isset($data['elements']['permissions']) ? $data['elements']['permissions'] : ''),
			'options' => (isset($data['elements']['options']) ? $data['elements']['options'] : '')
		);
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ?
				AND objectType = ?";
		$parameters = array(
			$this->installation->getPackageID(),
			$data['objectType']
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
}
