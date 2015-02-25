<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes user notification events.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
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
	 * preset event ids
	 * @var	array<integer>
	 */
	protected $presetEventIDs = array();
	
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
		
		$presetMailNotificationType = 'none';
		if (isset($data['elements']['presetmailnotificationtype']) && ($data['elements']['presetmailnotificationtype'] == 'instant' || $data['elements']['presetmailnotificationtype'] == 'daily')) {
			$presetMailNotificationType = $data['elements']['presetmailnotificationtype'];
		}
		
		return array(
			'eventName' => $data['elements']['name'],
			'className' => $data['elements']['classname'],
			'objectTypeID' => $objectTypeID,
			'permissions' => (isset($data['elements']['permissions']) ? $data['elements']['permissions'] : ''),
			'options' => (isset($data['elements']['options']) ? $data['elements']['options'] : ''),
			'preset' => (!empty($data['elements']['preset']) ? 1 : 0),
			'presetMailNotificationType' => $presetMailNotificationType
		);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::import()
	 */
	protected function import(array $row, array $data) {
		$result = parent::import($row, $data);
		
		if (empty($row) && $data['preset']) {
			$this->presetEventIDs[$result->eventID] = $data['presetMailNotificationType'];
		}
		
		return $result;
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::cleanup()
	 */
	protected function cleanup() {
		if (empty($this->presetEventIDs)) return;
		
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_notification_event_to_user
						(userID, eventID, mailNotificationType)
			SELECT			userID, ?, ?
			FROM			wcf".WCF_N."_user";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($this->presetEventIDs as $eventID => $mailNotificationType) {
			$statement->execute(array($eventID, $mailNotificationType));
		}
		WCF::getDB()->commitTransaction();
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
