<?php

namespace wcf\data\core\object;

use wcf\data\DatabaseObject;

/**
 * Represents a core object.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $objectID   unique id of the core object
 * @property-read   int $packageID  id of the package which delivers the core object
 * @property-read   string $objectName PHP class name of the core object
 */
class CoreObject extends DatabaseObject
{
}
