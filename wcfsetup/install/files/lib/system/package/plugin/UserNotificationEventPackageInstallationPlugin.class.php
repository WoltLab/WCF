<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes user notification events.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class UserNotificationEventPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\user\notification\event\UserNotificationEventEditor';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'user_notification_event';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */
	public $tagName = 'event';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND eventName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$this->installation->getPackageID(),
				$item['elements']['name']
			));
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		// get object type id
		$sql = "SELECT	object_type.objectTypeID
			FROM	wcf".WCF_N."_object_type object_type
			WHERE	object_type.objectType = ?
				AND object_type.definitionID IN (
					SELECT	definitionID
					FROM	wcf".WCF_N."_object_type_definition
					WHERE	definitionName = 'com.woltlab.wcf.notification.objectType'
				)";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute(array($data['elements']['objecttype']));
		$row = $statement->fetchArray();
		if (empty($row['objectTypeID'])) throw new SystemException("unknown notification object type '".$data['elements']['objecttype']."' given");
		$objectTypeID = $row['objectTypeID'];
		
		return array(
			'eventName' => $data['elements']['name'],
			'className' => $data['elements']['classname'],
			'objectTypeID' => $objectTypeID,
			'permissions' => (isset($data['elements']['permissions']) ? $data['elements']['permissions'] : ''),
			'options' => (isset($data['elements']['options']) ? $data['elements']['options'] : ''),
			'preset' => (!empty($data['elements']['preset']) ? 1 : 0)
		);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	objectTypeID = ?
				AND eventName = ?";
		$parameters = array(
			$data['objectTypeID'],
			$data['eventName']
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
}
