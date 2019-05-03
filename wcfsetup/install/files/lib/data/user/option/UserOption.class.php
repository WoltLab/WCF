<?php
namespace wcf\data\user\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Represents a user option.
 * 
 * @author	Joshua Ruesweg, Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Option
 * 
 * @property-read	string		$defaultValue		default value of the user option
 * @property-read	integer		$required		is `1` if the user option has to be filled out, otherwise `0`
 * @property-read	integer		$askDuringRegistration	is `1` if the user option will be shown during registration to be filled out, otherwise `0`
 * @property-read	integer		$editable		setting for who can edit the user option, see `UserOption::EDITABILITY_*` constants
 * @property-read	integer		$visible		setting for who can see the user option, see `UserOption::VISIBILITY_*` constants
 * @property-read	string		$outputClass		name of the PHP class implementing `wcf\system\option\user\IUserOptionOutput` for outputting the user option in the user profile
 * @property-read	integer		$searchable		is `1` if the user option can be searched, otherwise `0`
 * @property-read	integer		$isDisabled		is `1` if the user option is disabled and thus neither shown nor editable, otherwise `0`
 * @property-read	integer		$originIsSystem		is `1` if the user option was created by the system and not manually by an administrator, otherwise `0`
 */
class UserOption extends Option {
	/**
	 * visible for no one (no valid bit)
	 * @var	integer
	 */
	const VISIBILITY_NONE = 0;
	
	/**
	 * visible for the owner
	 * @var	integer
	 */
	const VISIBILITY_OWNER = 1;
	
	/**
	 * visible for admins
	 * @var	integer
	 */
	const VISIBILITY_ADMINISTRATOR = 2;
	
	/**
	 * visible for users
	 * @var	integer
	 */
	const VISIBILITY_REGISTERED = 4;
	
	/**
	 * visible for guests
	 * @var	integer
	 */
	const VISIBILITY_GUEST = 8;
	
	/**
	 * visible for all (no valid bit)
	 * @var	integer
	 */
	const VISIBILITY_ALL = 15;
	
	/**
	 * editable for no one (no valid bit)
	 * @var	integer
	 */
	const EDITABILITY_NONE = 0;
	
	/**
	 * editable for the owner
	 * @var	integer
	 */
	const EDITABILITY_OWNER = 1;
	
	/**
	 * editable for admins
	 * @var	integer
	 */
	const EDITABILITY_ADMINISTRATOR = 2;
	
	/**
	 * editable for all (no valid bit)
	 * @var	integer
	 */
	const EDITABILITY_ALL = 3;

	/**
	 * editable for owner during registration
	 * @var	integer
	 */
	const EDITABILITY_OWNER_DURING_REGISTRATION = 4;

	/**
	 * editable for owner during registration and admins (no valid bit)
	 * @var	integer
	 */
	const EDITABILITY_OWNER_DURING_REGISTRATION_AND_ADMINISTRATOR = 6;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_option';
	
	/**
	 * option value
	 * @var	string
	 */
	public $optionValue = '';
	
	/**
	 * user object
	 * @var	User
	 */
	public $user = null;
	
	/**
	 * Sets target user object.
	 * 
	 * @param	User	$user
	 */
	public function setUser(User $user) {
		$this->user = $user;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible() {
		// proceed if option is visible for all
		if ($this->visible & self::VISIBILITY_GUEST) {
			return true;
		}
		
		// proceed if option is visible for registered users and current user is logged in
		if (($this->visible & self::VISIBILITY_REGISTERED) && WCF::getUser()->userID) {
			return true;
		}
		
		// check admin permissions
		if ($this->visible & self::VISIBILITY_ADMINISTRATOR) {
			if (WCF::getSession()->getPermission('admin.general.canViewPrivateUserOptions')) {
				return true;
			}
		}
		
		// check owner state
		if ($this->visible & self::VISIBILITY_OWNER) {
			if ($this->user !== null && $this->user->userID == WCF::getUser()->userID) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns true iff this option is editable.
	 *
	 * @param	boolean		$inRegistration		True iff the user currently is in registration.
	 * @return	boolean
	 */
	public function isEditable($inRegistration = false) {
		// check admin permissions
		if ($this->editable & self::EDITABILITY_ADMINISTRATOR) {
			if (WCF::getSession()->getPermission('admin.general.canViewPrivateUserOptions')) {
				return true;
			}
		}
		
		// check owner state
		if ($this->editable & self::EDITABILITY_OWNER) {
			if ($this->user === null || $this->user->userID == WCF::getUser()->userID) {
				return true;
			}
		}
		
		if ($inRegistration && $this->editable & self::EDITABILITY_OWNER_DURING_REGISTRATION) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true iff this user option can be deleted.
	 * 
	 * @return	boolean
	 */
	public function canDelete() {
		if ($this->originIsSystem) {
			return false;
		}
		
		return true;
	}

	/**
	 * Allows modifications of editable option.
	 *
	 * @param int   $editableOption
	 */
	public function modifyEditableOption($editableOption) {
		$this->data['editable'] = $editableOption;
	}

	/**
	 * Allows modifications of visible option.
	 *
	 * @param int   $visibleOption
	 */
	public function modifyVisibleOption($visibleOption) {
		$this->data['visible'] = $visibleOption;
	}
}
