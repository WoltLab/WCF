<?php

namespace wcf\data\user\follow;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit followers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Follow
 *
 * @method static UserFollow  create(array $parameters = [])
 * @method      UserFollow  getDecoratedObject()
 * @mixin       UserFollow
 */
class UserFollowEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserFollow::class;
}
