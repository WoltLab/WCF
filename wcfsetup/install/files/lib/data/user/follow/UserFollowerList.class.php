<?php

namespace wcf\data\user\follow;

use wcf\data\user\User;
use wcf\data\user\UserProfile;

/**
 * Represents a list of followers.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserProfile     current()
 * @method  UserProfile[]       getObjects()
 * @method  UserProfile|null    getSingleObject()
 * @method  UserProfile|null    search($objectID)
 * @property    UserProfile[] $objects
 */
class UserFollowerList extends UserFollowList
{
    /**
     * @inheritDoc
     */
    public $className = UserFollow::class;

    /**
     * @inheritDoc
     */
    public $decoratorClassName = UserProfile::class;

    /**
     * @inheritDoc
     */
    public $objectClassName = User::class;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'user_follow.time DESC';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->sqlSelects .= "user_table.username, user_table.email, user_table.disableAvatar";
        $this->sqlSelects .= ", user_avatar.*";

        $this->sqlJoins .= "
            LEFT JOIN   wcf1_user user_table
            ON          user_table.userID = user_follow.userID
            LEFT JOIN   wcf1_user_avatar user_avatar
            ON          user_avatar.avatarID = user_table.avatarID";
    }
}
