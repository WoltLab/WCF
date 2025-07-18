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
use wcf\system\condition\type\user\IsEnabledConditionType;
use wcf\system\condition\type\user\IsNullUserConditionType;
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
 * @phpstan-type UserConditionType IDatabaseObjectListConditionType<UserList, mixed>&IObjectConditionType<User, mixed>
 * @extends AbstractConditionProvider<UserConditionType>
 */
final class UserConditionProvider extends AbstractConditionProvider
{
    public function __construct()
    {
        $this->addCondition(
            new StringUserConditionType(
                identifier: "username",
                columnName: "username",
                category: "user",
                migrateKeyName: "username",
                migrateConditionObjectType: 'com.woltlab.wcf.user.username'
            ),
        );
        $this->addCondition(
            new StringUserConditionType(
                identifier: "email",
                columnName: "email",
                category: "user",
                migrateKeyName: "email",
                migrateConditionObjectType: 'com.woltlab.wcf.user.email'
            ),
        );
        $this->addCondition(
            new RegistrationDateUserConditionType(),
        );
        $this->addCondition(
            new RegistrationDaysUserConditionType(),
        );
        $this->addCondition(
            new InGroupUserConditionType(),
        );
        $this->addCondition(
            new NotInGroupUserConditionType(),
        );
        $this->addCondition(
            new LanguageUserConditionType(),
        );
        $this->addCondition(
            new IsNullUserConditionType(
                identifier: "avatar",
                columnName: 'avatarFileID',
                category: 'userProfile',
                migrateKeyName: 'userAvatar',
                migrateConditionObjectType: 'com.woltlab.wcf.user.avatar'
            ),
        );
        $this->addCondition(
            new SignatureUserConditionType(),
        );
        $this->addCondition(
            new IsNullUserConditionType(
                identifier: "coverPhoto",
                columnName: 'coverPhotoFileID',
                category: 'userProfile',
                migrateKeyName: 'userCoverPhoto',
                migrateConditionObjectType: 'com.woltlab.wcf.coverPhoto'
            ),
        );
        $this->addCondition(
            new BooleanUserConditionType(
                identifier: "isBanned",
                columnName: 'banned',
                category: 'user',
                migrateKeyName: 'userIsBanned',
                migrateConditionObjectType: 'com.woltlab.wcf.user.state'
            ),
        );
        $this->addCondition(
            new IsEnabledConditionType(),
        );
        $this->addCondition(
            new IsNullUserConditionType(
                identifier: "isEmailConfirmed",
                columnName: 'emailConfirmed',
                category: 'user',
                migrateKeyName: 'userIsEmailConfirmed',
                migrateConditionObjectType: 'com.woltlab.wcf.user.state'
            ),
        );
        $this->addCondition(
            new BooleanUserConditionType(
                identifier: "isMultifactorActive",
                columnName: 'multifactorActive',
                category: 'user',
                migrateKeyName: 'multifactorActive',
                migrateConditionObjectType: 'com.woltlab.wcf.user.multifactor'
            ),
        );
        $this->addCondition(
            new HasTrophyUserConditionType(),
        );
        $this->addCondition(
            new HasNotTrophyUserConditionType(),
        );
        $this->addCondition(
            new IntegerUserConditionType(
                identifier: "activityPoints",
                columnName: "activityPoints",
                category: 'userProfile',
                migrateConditionObjectType: 'com.woltlab.wcf.user.activityPoints'
            ),
        );
        $this->addCondition(
            new IntegerUserConditionType(
                identifier: "likesReceived",
                columnName: "likesReceived",
                category: 'userProfile',
                migrateConditionObjectType: 'com.woltlab.wcf.user.likesReceived'
            ),
        );
        $this->addCondition(
            new IntegerUserConditionType(
                identifier: "trophyPoints",
                columnName: "trophyPoints",
                category: 'userProfile',
                migrateConditionObjectType: 'com.woltlab.wcf.user.trophyPoints'
            ),
        );

        // TODO add conditions for user options that implement `ISearchableConditionUserOption`

        EventHandler::getInstance()->fire(
            new UserConditionProviderCollecting($this)
        );
    }
}
