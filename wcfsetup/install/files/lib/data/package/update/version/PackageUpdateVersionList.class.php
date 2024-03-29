<?php

namespace wcf\data\package\update\version;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package update versions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  PackageUpdateVersion        current()
 * @method  PackageUpdateVersion[]      getObjects()
 * @method  PackageUpdateVersion|null   getSingleObject()
 * @method  PackageUpdateVersion|null   search($objectID)
 * @property    PackageUpdateVersion[] $objects
 */
class PackageUpdateVersionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = PackageUpdateVersion::class;
}
