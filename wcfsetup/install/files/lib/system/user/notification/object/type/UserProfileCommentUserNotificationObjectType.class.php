<?php
namespace wcf\system\user\notification\object\type;
use wcf\system\WCF;

/**
 * Represents a comment notification object type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object.type
 * @category	Community Framework
 */
class UserProfileCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements ICommentUserNotificationObjectType {
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$decoratorClassName
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\CommentUserNotificationObject';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$objectClassName
	 */
	protected static $objectClassName = 'wcf\data\comment\Comment';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\AbstractUserNotificationObjectType::$objectListClassName
	 */
	protected static $objectListClassName = 'wcf\data\comment\CommentList';
	
	/**
	 * @see	\wcf\system\user\notification\object\type\ICommentUserNotificationObjectType::getOwnerID()
	 */
	public function getOwnerID($objectID) {
		$sql = "SELECT	objectID
			FROM	wcf".WCF_N."_comment
			WHERE	commentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectID));
		$row = $statement->fetchArray();
		
		return ($row ? $row['objectID'] : 0);
	}
}
