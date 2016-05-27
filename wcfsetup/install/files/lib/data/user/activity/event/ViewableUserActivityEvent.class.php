<?php
namespace wcf\data\user\activity\event;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\user\activity\event\UserActivityEventHandler;

/**
 * Provides methods for viewable user activity events.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.activity.event
 * @category	Community Framework
 * 
 * @method	UserActivityEvent	getDecoratedObject()
 * @mixin	UserActivityEvent
 */
class ViewableUserActivityEvent extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = UserActivityEvent::class;
	
	/**
	 * event text
	 * @var	string
	 */
	protected $description = '';
	
	/**
	 * accessible by current user
	 * @var	boolean
	 */
	protected $isAccessible = false;
	
	/**
	 * associated object was removed
	 * @var	boolean
	 */
	protected $isOrphaned = false;
	
	/**
	 * event title
	 * @var	string
	 */
	protected $title = '';
	
	/**
	 * user profile
	 * @var	UserProfile
	 */
	protected $userProfile = null;
	
	/**
	 * Marks this event as accessible for current user.
	 */
	public function setIsAccessible() {
		$this->isAccessible = true;
	}
	
	/**
	 * Returns true if event is accessible by current user.
	 * 
	 * @return	boolean
	 */
	public function isAccessible() {
		return $this->isAccessible;
	}
	
	/**
	 * Marks this event as orphaned.
	 */
	public function setIsOrphaned() {
		$this->isOrphaned = true;
	}
	
	/**
	 * Returns true if event is orphaned (associated object removed).
	 * 
	 * @return	boolean
	 */
	public function isOrphaned() {
		return $this->isOrphaned;
	}
	
	/**
	 * Sets user profile.
	 * 
	 * @param	UserProfile	$userProfile
	 * @deprecated	since 2.2
	 */
	public function setUserProfile(UserProfile $userProfile) {
		$this->userProfile = $userProfile;
	}
	
	/**
	 * Returns user profile.
	 * 
	 * @return	UserProfile
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Sets event text.
	 * 
	 * @param	string		$description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	/**
	 * Returns event text.
	 * 
	 * @return	string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Sets event title.
	 * 
	 * @param	string		$title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Returns event title.
	 * 
	 * @return	string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Returns the object type name.
	 * 
	 * @return	string
	 */
	public function getObjectTypeName() {
		return UserActivityEventHandler::getInstance()->getObjectType($this->objectTypeID)->objectType;
	}
}
