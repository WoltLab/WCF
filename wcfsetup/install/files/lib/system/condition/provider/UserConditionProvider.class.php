<?php

namespace wcf\system\condition\provider;

use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\event\condition\provider\UserConditionProviderCollecting;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\condition\type\user\BooleanUserConditionType;
use wcf\system\condition\type\user\HasNotTrophyUserConditionType;
use wcf\system\condition\type\user\HasTrophyUserConditionType;
use wcf\system\condition\type\user\InGroupUserConditionType;
use wcf\system\condition\type\user\IntegerUserConditionType;
use wcf\system\condition\type\user\IsNullUserConditionType;
use wcf\system\condition\type\user\UserIsEnabledConditionType;
use wcf\system\condition\type\user\LanguageUserConditionType;
use wcf\system\condition\type\user\NotInGroupUserConditionType;
use wcf\system\condition\type\user\RegistrationDateUserConditionType;
use wcf\system\condition\type\user\RegistrationDaysUserConditionType;
use wcf\system\condition\type\user\SignatureUserConditionType;
use wcf\system\condition\type\user\StringUserConditionType;
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
            new StringUserConditionType(identifier: "username", columnName: "username", migrateKeyName: "username", migrateConditionObjectType: 'com.woltlab.wcf.user.username'),
            new StringUserConditionType(identifier: "email", columnName: "email", migrateKeyName: "email", migrateConditionObjectType: 'com.woltlab.wcf.user.email'),
            new RegistrationDateUserConditionType(),
            new RegistrationDaysUserConditionType(),
            new InGroupUserConditionType(),
            new NotInGroupUserConditionType(),
            new LanguageUserConditionType(),
            new IsNullUserConditionType(identifier: "avatar", columnName: 'avatarFileID', migrateKeyName: 'userAvatar', migrateConditionObjectType: 'com.woltlab.wcf.user.avatar'),
            new SignatureUserConditionType(),
            new IsNullUserConditionType(identifier: "coverPhoto", columnName: 'coverPhotoFileID', migrateKeyName: 'userCoverPhoto', migrateConditionObjectType: 'com.woltlab.wcf.coverPhoto'),
            new BooleanUserConditionType(identifier: "isBanned", columnName: 'banned', migrateKeyName: 'userIsBanned', migrateConditionObjectType: 'com.woltlab.wcf.user.state'),
            new UserIsEnabledConditionType(),
            new IsNullUserConditionType(identifier: "isEmailConfirmed", columnName: 'emailConfirmed', migrateKeyName: 'userIsEmailConfirmed', migrateConditionObjectType: 'com.woltlab.wcf.user.state'),
            new BooleanUserConditionType(identifier: "isMultifactorActive", columnName: 'multifactorActive', migrateKeyName: 'multifactorActive', migrateConditionObjectType: 'com.woltlab.wcf.user.multifactor'),
            new HasTrophyUserConditionType(),
            new HasNotTrophyUserConditionType(),
            new IntegerUserConditionType(identifier: "activityPoints", columnName: "activityPoints", migrateConditionObjectType: 'com.woltlab.wcf.user.activityPoints'),
            new IntegerUserConditionType(identifier: "likesReceived", columnName: "likesReceived", migrateConditionObjectType: 'com.woltlab.wcf.user.likesReceived'),
            new IntegerUserConditionType(identifier: "trophyPoints", columnName: "trophyPoints", migrateConditionObjectType: 'com.woltlab.wcf.user.trophyPoints'),
        ]);

        // TODO add conditions for user options that implement `ISearchableConditionUserOption`

        EventHandler::getInstance()->fire(
            new UserConditionProviderCollecting($this)
        );
    }
}
