<?php

namespace wcf\system\condition\provider;

use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\event\condition\provider\UserConditionProviderCollecting;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\condition\type\user\AbstractUserBooleanConditionType;
use wcf\system\condition\type\user\AbstractUserIntegerConditionType;
use wcf\system\condition\type\user\AbstractUserIsNullConditionType;
use wcf\system\condition\type\user\AbstractUserStringConditionType;
use wcf\system\condition\type\user\UserHasNotTrophyConditionType;
use wcf\system\condition\type\user\UserHasTrophyConditionType;
use wcf\system\condition\type\user\UserInGroupConditionType;
use wcf\system\condition\type\user\UserIsEnabledConditionType;
use wcf\system\condition\type\user\UserLanguageConditionType;
use wcf\system\condition\type\user\UserNotInGroupConditionType;
use wcf\system\condition\type\user\UserRegistrationDateConditionType;
use wcf\system\condition\type\user\UserRegistrationDaysConditionType;
use wcf\system\condition\type\user\UserSignatureConditionType;
use wcf\system\event\EventHandler;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractConditionProvider<IDatabaseObjectListConditionType<UserList, mixed>&IObjectConditionType<User, mixed>>
 */
final class UserConditionProvider extends AbstractConditionProvider
{
    public function __construct()
    {
        $this->addConditions([
            new class("username", "username", "username", 'com.woltlab.wcf.username') extends AbstractUserStringConditionType {},
            new class("email", "email", "email", 'com.woltlab.wcf.email') extends AbstractUserStringConditionType {},
            new UserRegistrationDateConditionType(),
            new UserRegistrationDaysConditionType(),
            new UserInGroupConditionType(),
            new UserNotInGroupConditionType(),
            new UserLanguageConditionType(),
            new class("avatar", 'avatarFileID') extends AbstractUserIsNullConditionType {},
            new UserSignatureConditionType(),
            new class("coverPhoto", 'coverPhotoFileID') extends AbstractUserIsNullConditionType {},
            new class("isBanned", 'banned') extends AbstractUserBooleanConditionType {},
            new UserIsEnabledConditionType(),
            new class("isEmailConfirmed", 'emailConfirmed') extends AbstractUserIsNullConditionType {},
            new class("isMultifactorActive", 'multifactorActive') extends AbstractUserBooleanConditionType {},
            new UserHasTrophyConditionType(),
            new UserHasNotTrophyConditionType(),
            new class("activityPoints", "activityPoints") extends AbstractUserIntegerConditionType {},
            new class("likesReceived", "likesReceived") extends AbstractUserIntegerConditionType {},
            new class("trophyPoints", "trophyPoints") extends AbstractUserIntegerConditionType {},
        ]);

        // TODO add conditions for user options that implement `ISearchableConditionUserOption`

        EventHandler::getInstance()->fire(
            new UserConditionProviderCollecting($this)
        );
    }
}
