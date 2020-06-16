<?php
namespace wcf\system\comment;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;

$_ = \wcf\functions\deprecatedClass(CommentDataHandler::class);
/**
 * Handles common data resources for comment-related user notifications
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Comment
 * @deprecated	3.0 Use CommentRuntimeCache to replace cacheCommentID() and getComment().
 *		Use UserProfileRuntimeCache to replace cacheUserID() and getUser().
 *		The cache*() methods are to be replaced by cacheObjectID() and the get*() methods
 *		are to be replaced by getObject().
 */
class CommentDataHandler extends SingletonFactory {
	/**
	 * @inheritDoc
	 */
	public function cacheCommentID($commentID) {
		CommentRuntimeCache::getInstance()->cacheObjectID($commentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function cacheUserID($userID) {
		UserProfileRuntimeCache::getInstance()->cacheObjectID($userID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getComment($commentID) {
		return CommentRuntimeCache::getInstance()->getObject($commentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUser($userID) {
		return UserProfileRuntimeCache::getInstance()->getObject($userID);
	}
}
