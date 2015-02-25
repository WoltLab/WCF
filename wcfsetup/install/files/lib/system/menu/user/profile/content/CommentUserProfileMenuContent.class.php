<?php
namespace wcf\system\menu\user\profile\content;
use wcf\system\comment\CommentHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile comment content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.user.profile.content
 * @category	Community Framework
 */
class CommentUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * comment manager object
	 * @var	\wcf\system\comment\manager\ICommentManager
	 */
	public $commentManager = null;
	
	/**
	 * object type id
	 * @var	integer
	 */
	public $objectTypeID = 0;
	
	/**
	 * @see	\wcf\system\menu\user\profile\content\IUserProfileMenuContent::getContent()
	 */
	public function getContent($userID) {
		if ($this->commentManager === null) {
			$this->objectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment');
			$objectType = CommentHandler::getInstance()->getObjectType($this->objectTypeID);
			$this->commentManager = $objectType->getProcessor();
		}
		
		$commentList = CommentHandler::getInstance()->getCommentList($this->commentManager, $this->objectTypeID, $userID);
		
		// assign variables
		WCF::getTPL()->assign(array(
			'commentCanAdd' => $this->commentManager->canAdd($userID),
			'commentList' => $commentList,
			'commentObjectTypeID' => $this->objectTypeID,
			'userID' => $userID,
			'lastCommentTime' => $commentList->getMinCommentTime(),
			'likeData' => (MODULE_LIKE ? $commentList->getLikeData() : array())
		));
		
		return WCF::getTPL()->fetch('userProfileCommentList');
	}
	
	/**
	 * @see	\wcf\system\menu\user\profile\content\IUserProfileMenuContent::isVisible()
	 */
	public function isVisible($userID) {
		return true;
	}
}
