<?php

namespace wcf\system\comment\manager;

use wcf\data\user\UserProfile;

/**
 * Interface for comment managers that provide permission checks.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ICommentPermissionManager extends ICommentManager
{
    /**
     * Returns true if the user may moderate content identified by
     * object type id and object id.
     */
    public function canModerateObject(int $objectTypeID, int $objectID, UserProfile $user): bool;

    /**
     * Returns true if the user may read content identified by object type id and object id.
     */
    public function canViewObject(int $objectID, UserProfile $user): bool;
}
