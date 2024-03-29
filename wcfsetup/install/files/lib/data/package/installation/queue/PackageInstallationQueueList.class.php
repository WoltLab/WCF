<?php

namespace wcf\data\package\installation\queue;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package installation queues.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  PackageInstallationQueue        current()
 * @method  PackageInstallationQueue[]      getObjects()
 * @method  PackageInstallationQueue|null       getSingleObject()
 * @method  PackageInstallationQueue|null       search($objectID)
 * @property    PackageInstallationQueue[] $objects
 */
class PackageInstallationQueueList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = PackageInstallationQueue::class;
}
