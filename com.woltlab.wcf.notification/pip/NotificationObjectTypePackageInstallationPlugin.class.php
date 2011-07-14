<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/NotificationTypePackageInstallationPlugin.class.php');

/**
 * Install, updates and uninstalls notification object types
 *
 * @author      Oliver Kliebisch
 * @copyright   2009-2010 Oliver Kliebisch
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     com.woltlab.community.wcf.user.notification
 * @subpackage  acp.package.plugin
 * @category    Community Framework
 */
class NotificationObjectTypePackageInstallationPlugin extends NotificationTypePackageInstallationPlugin {
	public $tagName = 'notificationobjecttype';
	public $tableName = 'user_notification_object_type';
	public $fieldName = 'objectType';
}
?>