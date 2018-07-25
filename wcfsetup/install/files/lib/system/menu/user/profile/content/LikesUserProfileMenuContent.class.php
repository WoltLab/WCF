<?php
declare(strict_types=1);
namespace wcf\system\menu\user\profile\content;
use wcf\data\like\ViewableLikeList;
use wcf\system\reaction\ReactionHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile likes content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Menu\User\Profile\Content
 */
class LikesUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * @inheritDoc
	 */
	public function getContent($userID) {
		$reactionTypes = ReactionHandler::getInstance()->getReactionTypes();
		$firstReactionType = reset($reactionTypes);
		
		$likeList = new ViewableLikeList();
		$likeList->getConditionBuilder()->add("like_table.objectUserID = ?", [$userID]);
		$likeList->getConditionBuilder()->add("like_table.reactionTypeID = ?", [$firstReactionType->reactionTypeID]);
		$likeList->readObjects();
		
		WCF::getTPL()->assign([
			'likeList' => $likeList,
			'userID' => $userID,
			'lastLikeTime' => $likeList->getLastLikeTime(), 
			'firstReactionTypeID' => $firstReactionType->reactionTypeID
		]);
		
		return WCF::getTPL()->fetch('userProfileLikes');
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($userID) {
		return true;
	}
}
