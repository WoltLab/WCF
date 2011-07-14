<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectEditor.class.php');
require_once(WCF_DIR.'lib/data/user/notification/object/UserNotificationObjectType.class.php');

/**
 * Extends the notification object type object with functions to create, update and delete messages.
 *
 * @author	Marcel Werk
 * @copyright	2009-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.object
 * @category 	Community Framework
 */
class UserNotificationObjectTypeEditor extends DatabaseObjectEditor {
	/**
	 * @see	DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'UserNotificationObjectType';
	
	/**
	 * @see	EditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		return self::__create('wcf'.WCF_N.'_user_notification_object_type', 'objectTypeID', 'UserNotificationObjectType', $parameters);
	}
	
	/**
	 * @see	EditableObject::deleteAll()
	 */
	public static function deleteAll(array $objectTypeIDs = array()) {
		return self::__deleteAll('wcf'.WCF_N.'_user_notification_object_type', 'objectTypeID', $$objectTypeIDs);
	}
}
?>