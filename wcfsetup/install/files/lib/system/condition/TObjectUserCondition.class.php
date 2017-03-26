<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\User;

/**
 * Redirects IUserCondition::checkUser() calls to the more general IObjectCondition::checkObject().
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 * @since	3.0
 */
trait TObjectUserCondition {
	/**
	 * @inheritDoc
	 */
	public function checkUser(Condition $condition, User $user) {
		return $this->checkObject($user, $condition->conditionData);
	}
}
