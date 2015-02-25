<?php
namespace wcf\data\comment;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\object\type\AbstractObjectTypeProvider;
use wcf\system\comment\CommentHandler;
use wcf\system\like\IViewableLikeProvider;

/**
 * Object type provider for comments
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 */
class LikeableCommentProvider extends AbstractObjectTypeProvider implements ILikeObjectTypeProvider, IViewableLikeProvider {
	/**
	 * @see	\wcf\data\object\type\AbstractObjectTypeProvider::$className
	 */
	public $className = 'wcf\data\comment\Comment';
	
	/**
	 * @see	\wcf\data\object\type\AbstractObjectTypeProvider::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\comment\LikeableComment';
	
	/**
	 * @see	\wcf\data\object\type\AbstractObjectTypeProvider::$listClassName
	 */
	public $listClassName = 'wcf\data\comment\CommentList';
	
	/**
	 * @see	\wcf\data\like\ILikeObjectTypeProvider::checkPermissions()
	 */
	public function checkPermissions(ILikeObject $comment) {
		if (!$comment->commentID) return false;
		
		$objectType = CommentHandler::getInstance()->getObjectType($comment->objectTypeID);
		return CommentHandler::getInstance()->getCommentManager($objectType->objectType)->isAccessible($comment->objectID);
	}
	
	/**
	 * @see	\wcf\system\like\IViewableLikeProvider::prepare()
	 */
	public function prepare(array $likes) {
		$commentIDs = array();
		foreach ($likes as $like) {
			$commentIDs[] = $like->objectID;
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->getConditionBuilder()->add("comment.commentID IN (?)", array($commentIDs));
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// group likes by object type id
		$likeData = array();
		foreach ($likes as $like) {
			if (isset($comments[$like->objectID])) {
				if (!isset($likeData[$comments[$like->objectID]->objectTypeID])) {
					$likeData[$comments[$like->objectID]->objectTypeID] = array();
				}
				$likeData[$comments[$like->objectID]->objectTypeID][] = $like;
			}
		}
		
		foreach ($likeData as $objectTypeID => $likes) {
			$objectType = CommentHandler::getInstance()->getObjectType($objectTypeID);
			if (CommentHandler::getInstance()->getCommentManager($objectType->objectType) instanceof IViewableLikeProvider) {
				CommentHandler::getInstance()->getCommentManager($objectType->objectType)->prepare($likes);
			}
		}
	}
}
