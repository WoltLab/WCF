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
 * @package	com.woltlab.wcf
 * @subpackage	system.comment
 * @category	Community Framework
 * @deprecated	since 2.2, use CommentRuntimeCache and UserProfileRuntimeCache
 */
class CommentDataHandler extends SingletonFactory {
	/**
	 * @see	CommentRuntimeCache::cacheObjectID()
	 */
	public function cacheCommentID($commentID) {
		CommentRuntimeCache::getInstance()->cacheObjectID($commentID);
	}
	
	/**
	 * @see	UserProfileRuntimeCache::cacheObjectID()
	 */
	public function cacheUserID($userID) {
		UserProfileRuntimeCache::getInstance()->cacheObjectID($userID);
	}
	
	/**
	 * @see	CommentRuntimeCache::getComment()
	 */
	public function getComment($commentID) {
		return CommentRuntimeCache::getInstance()->getObject($commentID);
	}
	
	/**
	 * @see	UserProfileRuntimeCache::getObject()
	 */
	public function getUser($userID) {
		return UserProfileRuntimeCache::getInstance()->getObject($userID);
	}
}
