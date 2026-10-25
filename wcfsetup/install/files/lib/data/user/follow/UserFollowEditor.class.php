<?php

namespace wcf\data\user\follow;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit followers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       UserFollow
 * @extends DatabaseObjectEditor<UserFollow>
 * @deprecated 6.3 use `UserFollowBuilder` instead.
 */
class UserFollowEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserFollow::class;
}
