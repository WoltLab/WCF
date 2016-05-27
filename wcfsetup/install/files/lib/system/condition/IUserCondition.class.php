<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;

/**
 * Every implementation for user conditions needs to implements this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
interface IUserCondition extends ICondition {
	/**
	 * Adds the condition to the given user list to fetch the users which fulfill
	 * the given condition.
	 * 
	 * @param	Condition	$condition
	 * @param	UserList	$userList
	 */
	public function addUserCondition(Condition $condition, UserList $userList);
	
	/**
	 * Returns true if the given user fulfills the given condition.
	 * 
	 * @param	Condition	$condition
	 * @param	User		$user
	 * @return	boolean
	 */
	public function checkUser(Condition $condition, User $user);
}
