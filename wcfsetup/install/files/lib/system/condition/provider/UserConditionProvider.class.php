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
            new class(identifier: "username", columnName: "username", migrateKeyName: "username", migrateConditionObjectType: 'com.woltlab.wcf.user.username') extends AbstractUserStringConditionType {},
            new class(identifier: "email", columnName: "email", migrateKeyName: "email", migrateConditionObjectType: 'com.woltlab.wcf.user.email') extends AbstractUserStringConditionType {},
            new UserRegistrationDateConditionType(),
            new UserRegistrationDaysConditionType(),
            new UserInGroupConditionType(),
            new UserNotInGroupConditionType(),
            new UserLanguageConditionType(),
            new class(identifier: "avatar", columnName: 'avatarFileID', migrateKeyName: 'userAvatar', migrateConditionObjectType: 'com.woltlab.wcf.user.avatar') extends AbstractUserIsNullConditionType {},
            new UserSignatureConditionType(),
            new class(identifier: "coverPhoto", columnName: 'coverPhotoFileID', migrateKeyName: 'userCoverPhoto', migrateConditionObjectType: 'com.woltlab.wcf.coverPhoto') extends AbstractUserIsNullConditionType {},
            new class(identifier: "isBanned", columnName: 'banned', migrateKeyName: 'userIsBanned', migrateConditionObjectType: 'com.woltlab.wcf.user.state') extends AbstractUserBooleanConditionType {},
            new UserIsEnabledConditionType(),
            new class(identifier: "isEmailConfirmed", columnName: 'emailConfirmed', migrateKeyName: 'userIsEmailConfirmed', migrateConditionObjectType: 'com.woltlab.wcf.user.state') extends AbstractUserIsNullConditionType {},
            new class(identifier: "isMultifactorActive", columnName: 'multifactorActive', migrateKeyName: 'multifactorActive', migrateConditionObjectType: 'com.woltlab.wcf.user.multifactor') extends AbstractUserBooleanConditionType {},
            new UserHasTrophyConditionType(),
            new UserHasNotTrophyConditionType(),
            new class(identifier: "activityPoints", columnName: "activityPoints", migrateConditionObjectType: 'com.woltlab.wcf.user.activityPoints') extends AbstractUserIntegerConditionType {},
            new class(identifier: "likesReceived", columnName: "likesReceived", migrateConditionObjectType: 'com.woltlab.wcf.user.likesReceived') extends AbstractUserIntegerConditionType {},
            new class(identifier: "trophyPoints", columnName: "trophyPoints", migrateConditionObjectType: 'com.woltlab.wcf.user.trophyPoints') extends AbstractUserIntegerConditionType {},
        ]);

        // TODO add conditions for user options that implement `ISearchableConditionUserOption`

        EventHandler::getInstance()->fire(
            new UserConditionProviderCollecting($this)
        );
    }
}
