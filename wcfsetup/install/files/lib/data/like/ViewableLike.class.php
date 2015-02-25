<?php
namespace wcf\data\like;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides methods for viewable likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like
 * @category	Community Framework
 */
class ViewableLike extends DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	public static $baseClass = 'wcf\data\like\Like';
	
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
	 * @var	\wcf\data\user\UserProfile
	 */
	protected $userProfile = null;
	
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
	 * @param	\wcf\data\user\UserProfile	$userProfile
	 */
	public function setUserProfile(UserProfile $userProfile) {
		$this->userProfile = $userProfile;
	}
	
	/**
	 * Returns user profile.
	 * 
	 * @return	\wcf\data\user\UserProfile
	 */
	public function getUserProfile() {
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
}
