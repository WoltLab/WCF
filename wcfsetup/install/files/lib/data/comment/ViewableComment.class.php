<?php
namespace wcf\data\comment;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable comment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 */
class ViewableComment extends DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\comment\Comment';
	
	/**
	 * user profile object
	 * @var	\wcf\data\user\UserProfile
	 */
	protected $userProfile = null;
	
	/**
	 * Returns the user profile object.
	 * 
	 * @return	\wcf\data\user\UserProfile
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new UserProfile(new User(null, $this->getDecoratedObject()->data));
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Gets a specific comment decorated as comment entry.
	 * 
	 * @param	integer		$commentID
	 * @return	\wcf\data\comment\ViewableComment
	 */
	public static function getComment($commentID) {
		$list = new ViewableCommentList();
		$list->setObjectIDs(array($commentID));
		$list->readObjects();
		$objects = $list->getObjects();
		if (isset($objects[$commentID])) return $objects[$commentID];
		return null;
	}
}
