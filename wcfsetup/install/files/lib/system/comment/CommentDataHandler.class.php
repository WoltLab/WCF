<?php
namespace wcf\system\comment;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;

/**
 * Handles common data resources for comment-related user notifications
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Comment
 * @deprecated	3.0, use CommentRuntimeCache and UserProfileRuntimeCache
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
