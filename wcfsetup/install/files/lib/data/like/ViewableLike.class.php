<?php
namespace wcf\data\like;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\WCF;

/**
 * Provides methods for viewable likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Like
 * 
 * @method	Like	getDecoratedObject()
 * @mixin	Like
 */
class ViewableLike extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = Like::class;
	
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
	 * event title
	 * @var	string
	 */
	protected $title = '';
	
	/**
	 * user profile
	 * @var	UserProfile
	 */
	protected $userProfile;
	
	/**
	 * description of the object type displayed in the list of likes
	 * @var		string
	 * @since	3.1
	 */
	protected $objectTypeDescription;
	
	/**
	 * Marks this like as accessible for current user.
	 */
	public function setIsAccessible() {
		$this->isAccessible = true;
	}
	
	/**
	 * Returns true if like is accessible by current user.
	 * 
	 * @return	boolean
	 */
	public function isAccessible() {
		return $this->isAccessible;
	}
	
	/**
	 * Sets user profile.
	 * 
	 * @param	UserProfile	$userProfile
	 * @deprecated	3.0
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
	 * Sets like description.
	 * 
	 * @param	string		$description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	/**
	 * Returns like description.
	 * 
	 * @return	string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Sets like title.
	 * 
	 * @param	string		$title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Returns like title.
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
		return ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->objectType;
	}
	
	/**
	 * Sets the description of the object type displayed in the list of likes.
	 * 
	 * @param	string		$name
	 * @since	3.1
	 */
	public function setObjectTypeDescription($name) {
		$this->objectTypeDescription = $name;
	}
	
	/**
	 * Returns the description of the object type displayed in the list of likes.
	 * 
	 * If no description has been set before, `wcf.like.objectType.{$this->getObjectTypeName()}`
	 * is returned. 
	 * 
	 * @return	string
	 * @since	3.1
	 */
	public function getObjectTypeDescription() {
		if ($this->objectTypeDescription !== null) {
			return $this->objectTypeDescription;
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.like.objectType.' . $this->getObjectTypeName());
	}
}
