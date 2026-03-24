<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\user\UserList;

/**
 * Redirects IUserCondition::addUserCondition() calls to the more general
 * IObjectListCondition::addObjectListCondition().
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
trait TObjectListUserCondition
{
    /**
     * @return void
     */
    public function addUserCondition(Condition $condition, UserList $userList)
    {
        $this->addObjectListCondition($userList, $condition->conditionData);
    }
}
