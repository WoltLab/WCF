<?php
namespace wcf\system\comment\manager;
use wcf\data\user\UserProfile;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * User profile comment manager implementation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.comment.manager
 * @category	Community Framework
 */
class UserProfileCommentManager extends AbstractCommentManager {
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionAdd
	 */
	protected $permissionAdd = 'user.profileComment.canAddComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionCanModerate
	 */
	protected $permissionCanModerate = 'mod.profileComment.canModerateComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionDelete
	 */
	protected $permissionDelete = 'user.profileComment.canDeleteComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionEdit
	 */
	protected $permissionEdit = 'user.profileComment.canEditComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionModDelete
	 */
	protected $permissionModDelete = 'mod.profileComment.canDeleteComment';
	
	/**
	 * @see	\wcf\system\comment\manager\AbstractCommentManager::$permissionModEdit
	 */
	protected $permissionModEdit = 'mod.profileComment.canEditComment';
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::isAccessible()
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		// check object id
		$userProfile = UserProfile::getUserProfile($objectID);
		if ($userProfile === null) {
			return false;
		}
		
		// check visibility
		if ($userProfile->isProtected()) {
			return false;
		}
		
		// check target user settings
		if ($validateWritePermission) {
			if (!$userProfile->isAccessible('canWriteProfileComments') && $userProfile->userID != WCF::getUser()->userID) {
				return false;
			}
			
			if ($userProfile->isIgnoredUser(WCF::getUser()->userID)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::getLink()
	 */
	public function getLink($objectTypeID, $objectID) {
		return LinkHandler::getInstance()->getLink('User', array('id' => $objectID));
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::getTitle()
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) return WCF::getLanguage()->get('wcf.user.profile.content.wall.commentResponse');
		
		return WCF::getLanguage()->getDynamicVariable('wcf.user.profile.content.wall.comment');
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::updateCounter()
	 */
	public function updateCounter($objectID, $value) { }
}
