<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\UserList;

/**
 * Redirects IUserCondition::addUserCondition() calls to the more general
 * IObjectListCondition::addObjectListCondition().
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
trait TObjectListUserCondition {
	/**
	 * @see	\wcf\system\condition\IUserCondition::addUserCondition()
	 */
	public function addUserCondition(Condition $condition, UserList $userList) {
		$this->addObjectListCondition($userList, $condition->conditionData);
	}
}
