<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\comment\Comment;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Implements IMultiRecipientCommentUserNotificationObjectType::getRecipientIDs()
 * for moderation queue comment user notification object types.
 *
 * @author	Mathias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since	3.0
 */
trait TMultiRecipientModerationQueueCommentUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	public function getRecipientIDs(Comment $comment) {
		$objectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.moderation.queue');
		if ($comment->objectTypeID != $objectTypeID) {
			return [];
		}
		
		// 1. fetch assigned user
		// 2. fetch users who commented on the moderation queue entry
		// 3. fetch users who responded to a comment on the moderation queue entry
		$sql = "(
				SELECT	assignedUserID
				FROM	wcf".WCF_N."_moderation_queue
				WHERE	queueID = ?
					AND assignedUserID IS NOT NULL
			)
			UNION
			(
				SELECT		DISTINCT userID
				FROM		wcf".WCF_N."_comment
				WHERE		objectID = ?
						AND objectTypeID = ?
			)
			UNION
			(
				SELECT		DISTINCT comment_response.userID
				FROM		wcf".WCF_N."_comment_response comment_response
				INNER JOIN	wcf".WCF_N."_comment comment
				ON		(comment.commentID = comment_response.commentID)
				WHERE		comment.objectID = ?
						AND comment.objectTypeID = ?
			)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$comment->objectID,
			$comment->objectID,
			$objectTypeID,
			$comment->objectID,
			$objectTypeID
		]);
		$recipientIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		// make sure that all users can (still) access the moderation queue entry
		if (!empty($recipientIDs)) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('userID IN (?)', [$recipientIDs]);
			$conditionBuilder->add('queueID = ?', [$comment->objectID]);
			$conditionBuilder->add('isAffected = ?', [1]);
			$sql = "SELECT		userID
				FROM		wcf".WCF_N."_moderation_queue_to_user
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			$recipientIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
			
			// make sure that all users (still) have permission to access moderation
			if (!$recipientIDs) {
				UserStorageHandler::getInstance()->loadStorage($recipientIDs);
				$userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($recipientIDs);
				$recipientIDs = array_keys(array_filter($userProfiles, function(UserProfile $userProfile) {
					return $userProfile->getPermission('mod.general.canUseModeration');
				}));
			}
		}
		
		return $recipientIDs;
	}
}
