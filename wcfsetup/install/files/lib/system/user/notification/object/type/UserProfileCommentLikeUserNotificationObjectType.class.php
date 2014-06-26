<?php
namespace wcf\system\user\notification\object\type;
use wcf\system\WCF;

/**
 * Represents a comment notification object type for likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object.type
 * @category	Community Framework
 */
class UserProfileCommentLikeUserNotificationObjectType extends AbstractUserNotificationObjectType implements ICommentUserNotificationObjectType {
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$decoratorClassName
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\CommentLikeUserNotificationObject';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$objectClassName
	 */
	protected static $objectClassName = 'wcf\data\like\Like';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$objectListClassName
	 */
	protected static $objectListClassName = 'wcf\data\like\LikeList';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\ICommentUserNotificationObjectType::getOwnerID()
	 */
	public function getOwnerID($objectID) {
		$sql = "SELECT	objectUserID
			FROM	wcf".WCF_N."_like
			WHERE	likeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectID));
		$row = $statement->fetchArray();
		
		return ($row ? $row['objectUserID'] : 0);
	}
}
