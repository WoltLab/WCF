<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\object\type\AbstractObjectTypeProvider;
use wcf\system\comment\CommentHandler;

/**
 * Object type provider for likeable comment responses.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class LikeableCommentResponseProvider extends AbstractObjectTypeProvider implements ILikeObjectTypeProvider {
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
}
