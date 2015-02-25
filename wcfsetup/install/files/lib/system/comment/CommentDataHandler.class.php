<?php
namespace wcf\system\comment;
use wcf\data\comment\CommentList;
use wcf\data\user\UserProfile;
use wcf\system\SingletonFactory;

/**
 * Handles common data resources for comment-related user notifications
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.comment
 * @category	Community Framework
 */
class CommentDataHandler extends SingletonFactory {
	/**
	 * list of comment ids
	 * @var	array<integer>
	 */
	protected $commentIDs = array();
	
	/**
	 * list of cached comment objects
	 * @var	array<\wcf\data\comment\Comment>
	 */
	protected $comments = array();
	
	/**
	 * list of user ids
	 * @var	array<integer>
	 */
	protected $userIDs = array();
	
	/**
	 * Caches a comment id.
	 * 
	 * @param	integer		$commentID
	 */
	public function cacheCommentID($commentID) {
		if (!in_array($commentID, $this->commentIDs)) {
			$this->commentIDs[] = $commentID;
		}
	}
	
	/**
	 * Caches a user id.
	 * 
	 * @param	integer		$userID
	 */
	public function cacheUserID($userID) {
		if (!in_array($userID, $this->userIDs)) {
			$this->userIDs[] = $userID;
		}
	}
	
	/**
	 * Returns a comment by id, fetches comments on first call.
	 * 
	 * @param	integer		$commentID
	 * @return	\wcf\data\comment\Comment
	 */
	public function getComment($commentID) {
		if (!empty($this->commentIDs)) {
			$this->commentIDs = array_diff($this->commentIDs, array_keys($this->comments));
			
			if (!empty($this->commentIDs)) {
				$commentList = new CommentList();
				$commentList->setObjectIDs($this->commentIDs);
				$commentList->readObjects();
				$this->comments += $commentList->getObjects();
				$this->commentIDs = array();
			}
		}
		
		if (isset($this->comments[$commentID])) {
			return $this->comments[$commentID];
		}
		
		return null;
	}
	
	/**
	 * Returns a user profile by id, fetches user profiles on first call.
	 * 
	 * @param	integer		$userID
	 * @return	\wcf\data\user\UserProfile
	 */
	public function getUser($userID) {
		if (!empty($this->userIDs)) {
			UserProfile::getUserProfiles($this->userIDs);
			$this->userIDs = array();
		}
		
		return UserProfile::getUserProfile($userID);
	}
}
