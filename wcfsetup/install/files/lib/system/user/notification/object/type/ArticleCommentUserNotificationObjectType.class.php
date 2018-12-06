<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\WCF;

/**
 * Represents a comment notification object type for comments on articles.
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since       3.2
 */
class ArticleCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements ICommentUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = CommentUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = Comment::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = CommentList::class;
	
	/**
	 * @inheritDoc
	 */
	public function getOwnerID($objectID) {
		$sql = "SELECT		article.userID
			FROM		wcf".WCF_N."_comment comment
			LEFT JOIN	wcf".WCF_N."_article article
			ON		(article.articleID = comment.objectID)
			WHERE		comment.commentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectID]);
		
		return $statement->fetchSingleColumn() ?: 0;
	}
}
