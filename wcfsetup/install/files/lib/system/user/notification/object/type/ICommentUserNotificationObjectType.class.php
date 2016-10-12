<?php
namespace wcf\system\user\notification\object\type;

/**
 * Default interface for comment user notification object types.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 */
interface ICommentUserNotificationObjectType {
	/**
	 * Returns owner id of comment context.
	 * 
	 * @param	integer		$objectID
	 * @return	integer
	 */
	public function getOwnerID($objectID);
}
