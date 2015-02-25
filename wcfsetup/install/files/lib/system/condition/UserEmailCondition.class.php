<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Condition implementation for the email address of a user.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserEmailCondition extends AbstractTextCondition implements IContentCondition, IUserCondition {
	/**
	 * @see	\wcf\system\condition\AbstractTextCondition::$fieldName
	 */
	protected $fieldName = 'email';
	
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::$label
	 */
	protected $label = 'wcf.user.email';
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::addUserCondition()
	 */
	public function addUserCondition(Condition $condition, UserList $userList) {
		$userList->getConditionBuilder()->add('user_table.email LIKE ?', array('%'.addcslashes($condition->email, '_%').'%'));
	}
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::checkUser()
	 */
	public function checkUser(Condition $condition, User $user) {
		return mb_strpos($user->email, $condition->email) !== false;
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		if (!WCF::getUser()->userID) return false;
		
		return $this->checkUser($condition, WCF::getUser());
	}
}
