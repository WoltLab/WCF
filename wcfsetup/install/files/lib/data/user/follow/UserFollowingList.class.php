<?php

namespace wcf\data\user\follow;

/**
 * Represents a list of following users.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserFollowingList extends UserFollowerList
{
    /**
     * @inheritDoc
     */
    public $useQualifiedShorthand = false;

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        UserFollowList::__construct();

        $this->sqlSelects .= "user_avatar.*, user_follow.followID, user_option_value.*";

        $this->sqlJoins .= "
            LEFT JOIN   wcf1_user user_table
            ON          user_table.userID = user_follow.followUserID
            LEFT JOIN   wcf1_user_option_value user_option_value
            ON          user_option_value.userID = user_table.userID
            LEFT JOIN   wcf1_user_avatar user_avatar
            ON          user_avatar.avatarID = user_table.avatarID";

        $this->sqlSelects .= ", user_table.*";
    }
}
