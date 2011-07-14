<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a notification object type.
 *
 * @author	Marcel Werk
 * @copyright	2009-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.object
 * @category 	Community Framework
 */
class UserNotificationObjectType extends DatabaseObject {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	public $databaseTableName = 'user_notification_object_type';
	
	/**
	 * @see	DatabaseObject::$databaseIndexName
	 */
	public $databaseIndexName = 'objectTypeID';
}
?>