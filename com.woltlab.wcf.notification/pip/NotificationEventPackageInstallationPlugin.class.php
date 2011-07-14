<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * Install, updates and uninstalls notification events
 *
 * @author      Oliver Kliebisch
 * @copyright   2009-2010 Oliver Kliebisch
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     com.woltlab.community.wcf.user.notification
 * @subpackage  acp.package.plugin
 * @category    Community Framework
 */
class NotificationEventPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'notificationevent';
	public $tableName = 'user_notification_event';

	/**
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();

		if (!$xml = $this->getXML()) {
			return;
		}

		// Create an array with the data blocks (import or delete) from the xml file.
		$notificationEventXML = $xml->getElementTree('data');

		// Loop through the array and install or uninstall items.
		foreach ($notificationEventXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $notificationEvent) {
						// Extract item properties.
						foreach ($notificationEvent['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$notificationEvent[$child['name']] = $child['cdata'];
						}

						// default values
						$name = $objectType = $classFile = $languageCategory = $defaultNotificationType = $icon = $acceptURL = $declineURL = $permissions = $options = '';
						$requiresConfirmation = 0;

						// get values
						if (isset($notificationEvent['name'])) $name = $notificationEvent['name'];
						if (isset($notificationEvent['objecttype'])) $objectType = $notificationEvent['objecttype'];
						if (isset($notificationEvent['classfile'])) $classFile = $notificationEvent['classfile'];
						if (isset($notificationEvent['languagecategory'])) $languageCategory = $notificationEvent['languagecategory'];
						if (isset($notificationEvent['defaultnotificationtype'])) $defaultNotificationType = $notificationEvent['defaultnotificationtype'];
						if (isset($notificationEvent['icon'])) $icon = $notificationEvent['icon'];
						if (isset($notificationEvent['requiresconfirmation'])) $requiresConfirmation = $notificationEvent['requiresconfirmation'] ? 1 : 0;
						if (isset($notificationEvent['accepturl'])) $acceptURL = $notificationEvent['accepturl'];
						if (isset($notificationEvent['declineurl'])) $declineURL = $notificationEvent['declineurl'];
						if (isset($notificationEvent['permissions'])) $permissions = $notificationEvent['permissions'];
						if (isset($notificationEvent['options'])) $options = $notificationEvent['options'];

						// insert items
						$sql = "INSERT INTO			wcf".WCF_N."_".$this->tableName."
											(packageID, eventName, objectType, classFile, languageCategory, defaultNotificationType, icon, requiresConfirmation, acceptURL, declineURL, permissions, options)
							VALUES				(".$this->installation->getPackageID().",
											'".escapeString($name)."',
											'".escapeString($objectType)."',
											'".escapeString($classFile)."',
											'".escapeString($languageCategory)."',
											'".escapeString($defaultNotificationType)."',
											'".escapeString($icon)."',
											'".escapeString($requiresConfirmation)."',
											'".escapeString($acceptURL)."',
											'".escapeString($declineURL)."',
											'".escapeString($permissions)."',
											'".escapeString($options)."')
							ON DUPLICATE KEY UPDATE 	objectType = VALUES(objectType),
											classFile = VALUES(classFile),
											languageCategory = VALUES(languageCategory),
											defaultNotificationType = VALUES(defaultNotificationType),
											icon = VALUES(icon),
											requiresConfirmation = VALUES(requiresConfirmation),
											acceptURL = VALUES(acceptURL),
											declineURL = VALUES(declineURL),
											permissions = VALUES(permissions),
											options = VALUES(options)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete' && $this->installation->getAction() == 'update') {
					// Loop through items and delete them.
					$nameArray = array();
					foreach ($block['children'] as $notificationEvent) {
						// Extract item properties.
						foreach ($notificationEvent['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$notificationEvent[$child['name']] = $child['cdata'];
						}

						if (empty($notificationEvent['name'])) {
							throw new SystemException("Required 'name' attribute for notification event is missing", 13023);
						}
						$nameArray[] = $notificationEvent['name'];
					}
					if (count($nameArray)) {
						$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
							WHERE		packageID = ".$this->installation->getPackageID()."
									AND eventName IN ('".implode("','", array_map('escapeString', $nameArray))."')";
						WCF::getDB()->sendQuery($sql);
					}
				}
			}
		}
	}
	
}
?>