<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/notification/object/UserNotificationConveyableObject.class.php');

/**
 * This interface defines the basic methods every notification object type should implement.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2009-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.object
 * @category 	Community Framework
 */
interface UserNotificationConveyableObjectType {
	/**
	 * Get the notification object by its ID
	 *
	 * @param	integer		$objectID
	 * @return      UserNotificationConveyableObject
	 */
	public function getObjectByID($objectID);

	/**
	 * Get the notification object by using its parent object
	 *
	 * @param	object  	$object
	 * @return	UserNotificationConveyableObject
	 */
	public function getObjectByObject($object);

	/**
	 * Get the notification objects by their IDs
	 *
	 * @param	array<integer>		$objectIDs
	 * @return	array<UserNotificationConveyableObject>
	 */
	public function getObjectsByIDs(array $objectIDs);

	/**
	 * Get the notification objects
	 *
	 * @param	mixed		$data
	 * @return	array<UserNotificationConveyableObject>
	 */
	public function getObjects($data);

	/**
	 * Returns the package ID of the object's package
	 * It does not return the package ID of the oject type package
	 *
	 * @return integer
	 */
	public function getPackageID();

	/**
	 * Returns additional packageIDs.
	 * This can be used for multiple combined dependency trees
	 *
	 * @return array<integer>
	 */
	public function getAdditionalPackageIDs();
}
?>