<?php
namespace wcf\system\menu\user\profile\content;
use wcf\data\like\Like;
use wcf\data\like\ViewableLikeList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile likes content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.user.profile.content
 * @category	Community Framework
 */
class LikesUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * @see	\wcf\system\menu\user\profile\content\IUserProfileMenuContent::getContent()
	 */
	public function getContent($userID) {
		$likeList = new ViewableLikeList();
		$likeList->getConditionBuilder()->add("like_table.objectUserID = ?", [$userID]);
		$likeList->getConditionBuilder()->add("like_table.likeValue = ?", [Like::LIKE]);
		$likeList->readObjects();
		
		WCF::getTPL()->assign([
			'likeList' => $likeList,
			'userID' => $userID,
			'lastLikeTime' => $likeList->getLastLikeTime(),
		]);
		
		return WCF::getTPL()->fetch('userProfileLikes');
	}
	
	/**
	 * @see	\wcf\system\menu\user\profile\content\IUserProfileMenuContent::isVisible()
	 */
	public function isVisible($userID) {
		return true;
	}
}
