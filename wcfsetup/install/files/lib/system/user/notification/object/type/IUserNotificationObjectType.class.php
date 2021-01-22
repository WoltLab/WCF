<?php

namespace wcf\system\user\notification\object\type;

use wcf\data\IDatabaseObjectProcessor;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * This interface defines the basic methods every notification object type should implement.
 *
 * @author  Marcel Werk, Oliver Kliebisch
 * @copyright   2001-2019 WoltLab GmbH, Oliver Kliebisch
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Notification\Object\Type
 */
interface IUserNotificationObjectType extends IDatabaseObjectProcessor
{
    /**
     * Returns notification objects by their IDs.
     *
     * @param int[] $objectIDs
     * @return  IUserNotificationObject[]
     */
    public function getObjectsByIDs(array $objectIDs);
}
