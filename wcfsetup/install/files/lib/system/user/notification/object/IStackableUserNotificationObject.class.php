<?php

namespace wcf\system\user\notification\object;

/**
 * This interface should be implemented by every object which supports stackable notifications.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  since 3.1
 */
interface IStackableUserNotificationObject extends IUserNotificationObject
{
    /**
     * Returns the ID of the related object.
     *
     * @return  int
     */
    public function getRelatedObjectID();
}
