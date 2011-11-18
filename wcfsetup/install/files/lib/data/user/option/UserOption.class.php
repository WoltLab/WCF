<?php
namespace wcf\data\user\option;
use wcf\data\option\Option;
use wcf\system\option\user\IUserOptionOutput;

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
	 * @see	wcf\data\option\Option::isVisible()
	 */
	public function isVisible() {
		$bitmask = $this->options[$optionName]->visible;
		// check if option is hidden
		if ($bitmask & Option::VISIBILITY_NONE) {
			$visible = false;
		}
		// proceed if option is visible for all
		else if ($bitmask & Option::VISIBILITY_OTHER) {
			$visible = true;
		}
		else {
			$isAdmin = $isOwner = $visible = false;
			// check admin permissions
			if ($bitmask & Option::VISIBILITY_ADMINISTRATOR) {
				if (WCF::getSession()->getPermission('admin.general.canViewPrivateUserOptions')) {
					$isAdmin = true;
				}
			}
			
			// check owner state
			if ($bitmask & Option::VISIBILITY_OWNER) {
				if ($user->userID == WCF::getUser()->userID) {
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
