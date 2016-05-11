<?php
namespace wcf\data\comment;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\TLegacyUserPropertyAccess;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Represents a viewable comment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 * 
 * @method	Comment		getDecoratedObject()
 * @mixin	Comment
 */
class ViewableComment extends DatabaseObjectDecorator {
	use TLegacyUserPropertyAccess;
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Comment::class;
	
	/**
	 * user profile of the comment author
	 * @var	UserProfile
	 */
	protected $userProfile = null;
	
	/**
	 * Returns the user profile object.
	 * 
	 * @return	UserProfile
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			if ($this->userID) {
				$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
			}
			else {
				$this->userProfile = UserProfile::getGuestUserProfile($this->username);
			}
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Returns a specific comment decorated as comment entry.
	 * 
	 * @param	integer		$commentID
	 * @return	ViewableComment
	 */
	public static function getComment($commentID) {
		$list = new ViewableCommentList();
		$list->setObjectIDs([$commentID]);
		$list->readObjects();
		$objects = $list->getObjects();
		if (isset($objects[$commentID])) return $objects[$commentID];
		return null;
	}
}
