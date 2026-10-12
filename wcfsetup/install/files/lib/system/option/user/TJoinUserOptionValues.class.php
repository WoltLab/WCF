<?php

namespace wcf\system\option\user;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;

/**
 * Joins the user option values to the conditions of a user list.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TJoinUserOptionValues
{
    /**
     * Joins the user option values when counting the objects or reading their
     * ids, `UserList` only joins them when reading the objects.
     *
     * @param DatabaseObjectList<DatabaseObject> $list
     */
    protected function joinUserOptionValues(DatabaseObjectList $list): void
    {
        if (\str_contains($list->sqlConditionJoins, 'wcf1_user_option_value user_option_value')) {
            return;
        }

        $list->sqlConditionJoins .= "
            LEFT JOIN   wcf1_user_option_value user_option_value
            ON          user_option_value.userID = user_table.userID";
    }
}
