<?php
namespace wcf\data\comment;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides methods to handle responses for this comment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 */
class StructuredComment extends DatabaseObjectDecorator implements \Countable, \Iterator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	public static $baseClass = 'wcf\data\comment\Comment';
	
	/**
	 * list of ordered responses
	 * @var	array<\wcf\data\comment\response\StructuredCommentResponse>
	 */
	protected $responses = array();
	
	/**
	 * deletable by current user
	 * @var	boolean
	 */
	public $deletable = false;
	
	/**
	 * editable by current user
	 * @var	boolean
	 */
	public $editable = false;
	
	/**
	 * iterator index
	 * @var	integer
	 */
	private $position = 0;
	
	/**
	 * user profile object
	 * @var	\wcf\data\user\UserProfile
	 */
	public $userProfile = null;
	
	/**
	 * Adds an response
	 * 
	 * @param	\wcf\data\comment\response\StructuredCommentResponse	$response
	 */
	public function addResponse(StructuredCommentResponse $response) {
		$this->responses[] = $response;
	}
	
	/**
	 * Returns the last responses for this comment.
	 * 
	 * @return	array<\wcf\data\comment\response\StructuredCommentReponse>
	 */
	public function getResponses() {
		return $this->responses;
	}
	
	/**
	 * Returns timestamp of oldest response loaded.
	 * 
	 * @return	integer
	 */
	public function getLastResponseTime() {
		$lastResponseTime = 0;
		foreach ($this->responses as $response) {
			if (!$lastResponseTime) {
				$lastResponseTime = $response->time;
			}
			
			$lastResponseTime = max($lastResponseTime, $response->time);
		}
		
		return $lastResponseTime;
	}
	
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
	 * Returns true if the comment is deletable by current user.
	 * 
	 * @return	boolean
	 */
	public function isDeletable() {
		return $this->deletable;
	}
	
	/**
	 * Returns true if the comment is editable by current user.
	 * 
	 * @return	boolean
	 */
	public function isEditable() {
		return $this->editable;
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->responses);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->responses[$this->position];
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->postition;
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		$this->position++;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->position = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->responses[$this->position]);
	}
}
