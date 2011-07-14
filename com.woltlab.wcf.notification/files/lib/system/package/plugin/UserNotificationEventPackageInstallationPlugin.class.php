<?php
namespace wcf\system\package\plugin;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes user notification events.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class UserNotificationEventPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\user\notification\event\UserNotificationEventEditor';
	
	/**
	 * @see AbstractXMLPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'user_notification_event';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'event';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::handleDelete()
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
	 * @see	AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$objectTypeID = 0;
		$defaultNotificationTypeID = 0;
		
		return array(
			'eventName' => $data['elements']['name'],
			'className' => $data['elements']['classname'],
			'objectTypeID' => $objectTypeID,
			'defaultNotificationTypeID' => $defaultNotificationTypeID,
			'languageCategory' => (isset($data['elements']['languagecategory']) ? $data['elements']['languagecategory'] : ''),
			'requiresConfirmation' => (isset($data['elements']['requiresconfirmation']) ? intval($data['elements']['requiresconfirmation']) : 0),
			'acceptURL' => (isset($data['elements']['accepturl']) ? $data['elements']['accepturl'] : ''),
			'declineURL' => (isset($data['elements']['declineurl']) ? $data['elements']['declineurl'] : ''),
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
				AND eventName = ?";
		$parameters = array(
			$this->installation->getPackageID(),
			$data['eventName']
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
}
