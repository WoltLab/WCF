<?php

namespace wcf\system\condition\provider;

use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\event\condition\provider\UserConditionProviderCollecting;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\condition\type\user\AbstractBooleanUserConditionType;
use wcf\system\condition\type\user\AbstractIntegerUserConditionType;
use wcf\system\condition\type\user\AbstractIsNullUserConditionType;
use wcf\system\condition\type\user\AbstractStringUserConditionType;
use wcf\system\condition\type\user\HasNotTrophyUserConditionType;
use wcf\system\condition\type\user\HasTrophyUserConditionType;
use wcf\system\condition\type\user\InGroupUserConditionType;
use wcf\system\condition\type\user\UserIsEnabledConditionType;
use wcf\system\condition\type\user\LanguageUserConditionType;
use wcf\system\condition\type\user\NotInGroupUserConditionType;
use wcf\system\condition\type\user\RegistrationDateUserConditionType;
use wcf\system\condition\type\user\RegistrationDaysUserConditionType;
use wcf\system\condition\type\user\SignatureUserConditionType;
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
            new class(identifier: "username", columnName: "username", migrateKeyName: "username", migrateConditionObjectType: 'com.woltlab.wcf.user.username') extends AbstractStringUserConditionType {},
            new class(identifier: "email", columnName: "email", migrateKeyName: "email", migrateConditionObjectType: 'com.woltlab.wcf.user.email') extends AbstractStringUserConditionType {},
            new RegistrationDateUserConditionType(),
            new RegistrationDaysUserConditionType(),
            new InGroupUserConditionType(),
            new NotInGroupUserConditionType(),
            new LanguageUserConditionType(),
            new class(identifier: "avatar", columnName: 'avatarFileID', migrateKeyName: 'userAvatar', migrateConditionObjectType: 'com.woltlab.wcf.user.avatar') extends AbstractIsNullUserConditionType {},
            new SignatureUserConditionType(),
            new class(identifier: "coverPhoto", columnName: 'coverPhotoFileID', migrateKeyName: 'userCoverPhoto', migrateConditionObjectType: 'com.woltlab.wcf.coverPhoto') extends AbstractIsNullUserConditionType {},
            new class(identifier: "isBanned", columnName: 'banned', migrateKeyName: 'userIsBanned', migrateConditionObjectType: 'com.woltlab.wcf.user.state') extends AbstractBooleanUserConditionType {},
            new UserIsEnabledConditionType(),
            new class(identifier: "isEmailConfirmed", columnName: 'emailConfirmed', migrateKeyName: 'userIsEmailConfirmed', migrateConditionObjectType: 'com.woltlab.wcf.user.state') extends AbstractIsNullUserConditionType {},
            new class(identifier: "isMultifactorActive", columnName: 'multifactorActive', migrateKeyName: 'multifactorActive', migrateConditionObjectType: 'com.woltlab.wcf.user.multifactor') extends AbstractBooleanUserConditionType {},
            new HasTrophyUserConditionType(),
            new HasNotTrophyUserConditionType(),
            new class(identifier: "activityPoints", columnName: "activityPoints", migrateConditionObjectType: 'com.woltlab.wcf.user.activityPoints') extends AbstractIntegerUserConditionType {},
            new class(identifier: "likesReceived", columnName: "likesReceived", migrateConditionObjectType: 'com.woltlab.wcf.user.likesReceived') extends AbstractIntegerUserConditionType {},
            new class(identifier: "trophyPoints", columnName: "trophyPoints", migrateConditionObjectType: 'com.woltlab.wcf.user.trophyPoints') extends AbstractIntegerUserConditionType {},
        ]);

        // TODO add conditions for user options that implement `ISearchableConditionUserOption`

        EventHandler::getInstance()->fire(
            new UserConditionProviderCollecting($this)
        );
    }
}
