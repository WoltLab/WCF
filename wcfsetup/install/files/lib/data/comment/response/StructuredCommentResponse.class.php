<?php
namespace wcf\data\comment\response;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides methods to handle response data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class StructuredCommentResponse extends DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	public static $baseClass = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * deletable by current user
	 * @var	boolean
	 */
	public $deletable = false;
	
	/**
	 * editable for current user
	 * @var	boolean
	 */
	public $editable = false;
	
	/**
	 * user profile object
	 * @var	\wcf\data\user\UserProfile
	 */
	public $userProfile = null;
	
	/**
	 * Sets the user's profile.
	 * 
	 * @param	\wcf\data\user\UserProfile	$userProfile
	 */
	public function setUserProfile(UserProfile $userProfile) {
		$this->userProfile = $userProfile;
	}
	
	/**
	 * Returns the user's profile.
	 * 
	 * @return	\wcf\data\user\UserProfile
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new UserProfile(new User(null, $this->data));
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Returns a structured response.
	 * 
	 * @param	integer		$responseID
	 * @return	\wcf\data\comment\response\StructuredCommentResponse
	 */
	public static function getResponse($responseID) {
		$response = new CommentResponse($responseID);
		if (!$response->responseID) {
			return null;
		}
		
		// prepare structured response
		$response = new StructuredCommentResponse($response);
		
		// add user profile
		$userProfile = UserProfile::getUserProfile($response->userID);
		$response->setUserProfile($userProfile);
		
		return $response;
	}
	
	/**
	 * Sets deletable state.
	 * 
	 * @param	boolean		$deletable
	 */
	public function setIsDeletable($deletable) {
		$this->deletable = $deletable;
	}
	
	/**
	 * Sets editable state.
	 * 
	 * @param	boolean		$editable
	 */
	public function setIsEditable($editable) {
		$this->editable = $editable;
	}
	
	/**
	 * Returns true if the response is deletable by current user.
	 * 
	 * @return	boolean
	 */
	public function isDeletable() {
		return $this->deletable;
	}
	
	/**
	 * Returns true if the response is editable by current user.
	 * 
	 * @return	boolean
	 */
	public function isEditable() {
		return $this->editable;
	}
}
