<?php

namespace wcf\system\condition\provider;

use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\event\condition\provider\UserConditionProviderCollecting;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\condition\type\user\UserInGroupConditionType;
use wcf\system\condition\type\user\UserNotInGroupConditionType;
use wcf\system\condition\type\user\UserRegistrationDateConditionType;
use wcf\system\condition\type\user\UserRegistrationDaysConditionType;
use wcf\system\condition\type\user\UserUsernameConditionType;
use wcf\system\event\EventHandler;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractConditionProvider<IDatabaseObjectListConditionType<UserList>&IObjectConditionType<User>>
 */
final class UserConditionProvider extends AbstractConditionProvider
{
    public function __construct()
    {
        $this->addConditions([
            new UserUsernameConditionType(),
            new UserRegistrationDateConditionType(),
            new UserRegistrationDaysConditionType(),
            new UserInGroupConditionType(),
            new UserNotInGroupConditionType(),
        ]);

        EventHandler::getInstance()->fire(
            new UserConditionProviderCollecting($this)
        );
    }
}
