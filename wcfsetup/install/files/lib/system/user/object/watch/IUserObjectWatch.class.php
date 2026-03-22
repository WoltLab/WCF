<?php

namespace wcf\system\user\object\watch;

/**
 * Any watchable object type should implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IUserObjectWatch
{
    /**
     * Validates the given object id. Throws an exception on error.
     *
     * @return void
     * @throws  \wcf\system\exception\UserException
     */
    public function validateObjectID(int $objectID);

    /**
     * Resets the user storage for given users.
     *
     * @param int[] $userIDs
     * @return void
     */
    public function resetUserStorage(array $userIDs);
}
