<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * Install, updates and uninstalls notification types
 *
 * @author      Oliver Kliebisch
 * @copyright   2009-2010 Oliver Kliebisch
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     com.woltlab.community.wcf.user.notification
 * @subpackage  acp.package.plugin
 * @category    Community Framework
 */
class NotificationTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'notificationtype';
	public $tableName = 'user_notification_type';
	public $fieldName = 'notificationType';

	/**
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();

		if (!$xml = $this->getXML()) {
			return;
		}

		// Create an array with the data blocks (import or delete) from the xml file.
		$notificationTypeXML = $xml->getElementTree('data');

		// Loop through the array and install or uninstall items.
		foreach ($notificationTypeXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $notificationType) {
						// Extract item properties.
						foreach ($notificationType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$notificationType[$child['name']] = $child['cdata'];
						}

						// default values
						$name = $classFile = $permissions = $options = '';

						// get values
						if (isset($notificationType['name'])) $name = $notificationType['name'];
						if (isset($notificationType['classfile'])) $classFile = $notificationType['classfile'];
						if (isset($notificationType['permissions'])) $permissions = $notificationType['permissions'];
						if (isset($notificationType['options'])) $options = $notificationType['options'];

						// insert items
						$sql = "INSERT INTO			wcf".WCF_N."_".$this->tableName."
											(packageID, ".$this->fieldName.", classFile, permissions, options)
							VALUES				(".$this->installation->getPackageID().",
											'".escapeString($name)."',
											'".escapeString($classFile)."',
											'".escapeString($permissions)."',
											'".escapeString($options)."')
							ON DUPLICATE KEY UPDATE 	classFile = VALUES(classFile),
											permissions = VALUES(permissions),
											options = VALUES(options)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete' && $this->installation->getAction() == 'update') {
					// Loop through items and delete them.
					$nameArray = array();
					foreach ($block['children'] as $notificationType) {
						// Extract item properties.
						foreach ($notificationType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$notificationType[$child['name']] = $child['cdata'];
						}

						if (empty($notificationType['name'])) {
							throw new SystemException("Required 'name' attribute for ".$this->fieldName." is missing", 13023);
						}
						$nameArray[] = $notificationType['name'];
					}
					if (count($nameArray)) {
						$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
							WHERE		packageID = ".$this->installation->getPackageID()."
									AND ".$this->fieldName." IN ('".implode("','", array_map('escapeString', $nameArray))."')";
						WCF::getDB()->sendQuery($sql);
					}
				}
			}
		}
	}
}
?>