<?php
namespace wcf\data\user\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Represents a user option.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category	Community Framework
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
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_option';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'optionID';
	
	/**
	 * option value
	 * @var	string
	 */
	public $optionValue = '';
	
	/**
	 * user object
	 * @var	\wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * Sets target user object.
	 * 
	 * @param	\wcf\data\user\User	$user
	 */
	public function setUser(User $user) {
		$this->user = $user;
	}
	
	/**
	 * @see	\wcf\data\option\Option::isVisible()
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
	 * Returns true if this option is editable.
	 * 
	 * @return	boolean
	 */
	public function isEditable() {
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
		
		return false;
	}
	
	/**
	 * Returns true if this user option can be deleted.
	 * 
	 * @return	boolean
	 */
	public function canDelete() {
		if ($this->originIsSystem) {
			return false;
		}
		
		return true;
	}
}
