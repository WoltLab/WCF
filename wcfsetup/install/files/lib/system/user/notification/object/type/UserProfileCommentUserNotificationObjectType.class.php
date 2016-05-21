<?php
namespace wcf\system\user\notification\object\type;
use wcf\system\WCF;

/**
 * Represents a comment notification object type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object.type
 * @category	Community Framework
 */
class UserProfileCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements ICommentUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\CommentUserNotificationObject';
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = 'wcf\data\comment\Comment';
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = 'wcf\data\comment\CommentList';
	
	/**
	 * @inheritDoc
	 */
	public function getOwnerID($objectID) {
		$sql = "SELECT	objectID
			FROM	wcf".WCF_N."_comment
			WHERE	commentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectID]);
		$row = $statement->fetchArray();
		
		return ($row ? $row['objectID'] : 0);
	}
}
