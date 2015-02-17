<?php
namespace wcf\data\comment\response;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable comment response.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class ViewableCommentResponse extends DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\comment\response\CommentResponse';
	
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
	 * Gets a specific comment response decorated as viewable comment response.
	 * 
	 * @param	integer		$responseID
	 * @return	\wcf\data\comment\response\ViewableCommentResponse
	 */
	public static function getResponse($responseID) {
		$list = new ViewableCommentResponseList();
		$list->setObjectIDs(array($responseID));
		$list->readObjects();
		$objects = $list->getObjects();
		if (isset($objects[$responseID])) return $objects[$responseID];
		return null;
	}
}
