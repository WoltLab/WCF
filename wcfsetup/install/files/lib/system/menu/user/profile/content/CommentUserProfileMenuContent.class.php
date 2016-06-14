<?php
namespace wcf\system\menu\user\profile\content;
use wcf\system\comment\CommentHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile comment content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Menu\User\Profile\Content
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
	 * @inheritDoc
	 */
	public function getContent($userID) {
		if ($this->commentManager === null) {
			$this->objectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment');
			$objectType = CommentHandler::getInstance()->getObjectType($this->objectTypeID);
			$this->commentManager = $objectType->getProcessor();
		}
		
		$commentList = CommentHandler::getInstance()->getCommentList($this->commentManager, $this->objectTypeID, $userID);
		
		// assign variables
		WCF::getTPL()->assign([
			'commentCanAdd' => $this->commentManager->canAdd($userID),
			'commentList' => $commentList,
			'commentObjectTypeID' => $this->objectTypeID,
			'userID' => $userID,
			'lastCommentTime' => $commentList->getMinCommentTime(),
			'likeData' => (MODULE_LIKE ? $commentList->getLikeData() : [])
		]);
		
		return WCF::getTPL()->fetch('userProfileCommentList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($userID) {
		return true;
	}
}
