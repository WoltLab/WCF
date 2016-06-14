<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\data\user\UserList;

/**
 * Searchable user option types available for conditions have to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
interface ISearchableConditionUserOption extends ISearchableUserOption {
	/**
	 * Adds the condition to the given user list to fetch the users which have
	 * the given value for the given option.
	 * 
	 * @param	UserList	$userList
	 * @param	Option		$option
	 * @param	mixed		$value
	 */
	public function addCondition(UserList $userList, Option $option, $value);
	
	/**
	 * Returns true if given the user option of the given user matches a certain
	 * value.
	 * 
	 * @param	User		$user
	 * @param	Option		$option
	 * @param	mixed		$value
	 * @return	boolean
	 */
	public function checkUser(User $user, Option $option, $value);
	
	/**
	 * Returns the data of the condition or null if the option should be igored.
	 * 
	 * @param	Option		$option
	 * @param	mixed		$newValue
	 * @return	mixed
	 */
	public function getConditionData(Option $option, $newValue);
}
