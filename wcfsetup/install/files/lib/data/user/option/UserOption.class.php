<?php
namespace wcf\data\user\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\system\option\user\IUserOptionOutput;
use wcf\system\WCF;

/**
 * Represents a user option.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOption extends Option {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_option';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'optionID';
	
	/**
	 * option value
	 * @var	string
	 */
	public $optionValue = '';
	
	/**
	 * output data
	 * @var	array
	 */
	public $outputData = array();
	
	/**
	 * user object
	 * @var	wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * Sets target user object.
	 * 
	 * @param	wcf\data\user\User	$user
	 */
	public function setUser(User $user) {
		$this->user = $user;
	}
	
	/**
	 * @see	wcf\data\option\Option::isVisible()
	 */
	public function isVisible() {
		// check if option is hidden
		if (!$this->visible) {
			return false;
		}
		
		// proceed if option is visible for all
		if ($this->visible & Option::VISIBILITY_GUEST) {
			$visible = true;
		}
		// proceed if option is visible for registered users and current user is logged in
		else if (($this->visible & Option::VISIBILITY_REGISTERED) && WCF::getUser()->userID) {
			$visible = true;
		}
		else {
			$isAdmin = $isOwner = $visible = false;
			// check admin permissions
			if ($this->visible & Option::VISIBILITY_ADMINISTRATOR) {
				if (WCF::getSession()->getPermission('admin.general.canViewPrivateUserOptions')) {
					$isAdmin = true;
				}
			}
			
			// check owner state
			if ($this->visible & Option::VISIBILITY_OWNER) {
				if ($this->user->userID == WCF::getUser()->userID) {
					$isOwner = true;
				}
			}
			
			if ($isAdmin) {
				$visible = true;
			}
			else if ($isOwner) {
				$visible = true;
			}
		}
		
		if (!$visible || $this->disabled) return false;
		
		return true;
	}
}
