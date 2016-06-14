<?php
namespace wcf\system\package\plugin;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\event\UserNotificationEventEditor;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes user notification events.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class UserNotificationEventPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = UserNotificationEventEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tableName = 'user_notification_event';
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'event';
	
	/**
	 * preset event ids
	 * @var	integer[]
	 */
	protected $presetEventIDs = [];
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND eventName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$this->installation->getPackageID(),
				$item['elements']['name']
			]);
		}
	}
	
	/**
	 * @inheritDoc
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
		$statement->execute([$data['elements']['objecttype']]);
		$row = $statement->fetchArray();
		if (empty($row['objectTypeID'])) throw new SystemException("unknown notification object type '".$data['elements']['objecttype']."' given");
		$objectTypeID = $row['objectTypeID'];
		
		$presetMailNotificationType = 'none';
		if (isset($data['elements']['presetmailnotificationtype']) && ($data['elements']['presetmailnotificationtype'] == 'instant' || $data['elements']['presetmailnotificationtype'] == 'daily')) {
			$presetMailNotificationType = $data['elements']['presetmailnotificationtype'];
		}
		
		return [
			'eventName' => $data['elements']['name'],
			'className' => $data['elements']['classname'],
			'objectTypeID' => $objectTypeID,
			'permissions' => (isset($data['elements']['permissions']) ? $data['elements']['permissions'] : ''),
			'options' => (isset($data['elements']['options']) ? $data['elements']['options'] : ''),
			'preset' => (!empty($data['elements']['preset']) ? 1 : 0),
			'presetMailNotificationType' => $presetMailNotificationType
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		/** @var UserNotificationEvent $event */
		$event = parent::import($row, $data);
		
		if (empty($row) && $data['preset']) {
			$this->presetEventIDs[$event->eventID] = $data['presetMailNotificationType'];
		}
		
		return $event;
	}
	
	/**
	 * @inheritDoc
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
			$statement->execute([$eventID, $mailNotificationType]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	objectTypeID = ?
				AND eventName = ?";
		$parameters = [
			$data['objectTypeID'],
			$data['eventName']
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
}
