<?php
namespace wcf\data\comment\response;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\TLegacyUserPropertyAccess;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Represents a viewable comment response.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment\Response
 * 
 * @method	CommentResponse		getDecoratedObject()
 * @mixin	CommentResponse
 */
class ViewableCommentResponse extends DatabaseObjectDecorator {
	use TLegacyUserPropertyAccess;
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CommentResponse::class;
	
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
	 * Returns a specific comment response decorated as viewable comment response.
	 * 
	 * @param	integer		$responseID
	 * @return	ViewableCommentResponse
	 */
	public static function getResponse($responseID) {
		$list = new ViewableCommentResponseList();
		$list->setObjectIDs([$responseID]);
		$list->readObjects();
		$objects = $list->getObjects();
		if (isset($objects[$responseID])) return $objects[$responseID];
		return null;
	}
}
