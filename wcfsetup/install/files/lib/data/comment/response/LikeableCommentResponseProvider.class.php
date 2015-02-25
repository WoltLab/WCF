<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\object\type\AbstractObjectTypeProvider;
use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Object type provider for likeable comment responses.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class LikeableCommentResponseProvider extends AbstractObjectTypeProvider implements ILikeObjectTypeProvider, IViewableLikeProvider {
	/**
	 * @see	\wcf\data\object\type\AbstractObjectTypeProvider::$className
	 */
	public $className = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * @see	\wcf\data\object\type\AbstractObjectTypeProvider::$decoratorClassName
	 */
	public $decoratorClassName = 'wcf\data\comment\response\LikeableCommentResponse';
	
	/**
	 * @see	\wcf\data\object\type\AbstractObjectTypeProvider::$listClassName
	 */
	public $listClassName = 'wcf\data\comment\response\CommentResponseList';
	
	/**
	 * @see	\wcf\data\like\ILikeObjectTypeProvider::checkPermissions()
	 */
	public function checkPermissions(ILikeObject $response) {
		if (!$response->responseID) return false;
		$comment = new Comment($response->commentID);
		if (!$comment->commentID) {
			return false;
		}
		
		$objectType = CommentHandler::getInstance()->getObjectType($comment->objectTypeID);
		return CommentHandler::getInstance()->getCommentManager($objectType->objectType)->isAccessible($comment->objectID);
	}
	
	/**
	 * @see	\wcf\system\like\IViewableLikeProvider::prepare()
	 */
	public function prepare(array $likes) {
		$responseIDs = array();
		foreach ($likes as $like) {
			$responseIDs[] = $like->objectID;
		}
		
		// get objects type ids
		$responses = array();
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('comment_response.responseID IN (?)', array($responseIDs));
		$sql = "SELECT		comment.objectTypeID, comment_response.responseID
			FROM		wcf".WCF_N."_comment_response comment_response
			LEFT JOIN	wcf".WCF_N."_comment comment
			ON		(comment.commentID = comment_response.commentID)
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$responses[$row['responseID']] = $row['objectTypeID'];
		}
		
		// group likes by object type id
		$likeData = array();
		foreach ($likes as $like) {
			if (isset($responses[$like->objectID])) {
				if (!isset($likeData[$responses[$like->objectID]])) {
					$likeData[$responses[$like->objectID]] = array();
				}
				$likeData[$responses[$like->objectID]][] = $like;
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
