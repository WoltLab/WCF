<?php

namespace wcf\data\package\installation\plugin;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package installation plugins.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  PackageInstallationPlugin       current()
 * @method  PackageInstallationPlugin[]     getObjects()
 * @method  PackageInstallationPlugin|null      getSingleObject()
 * @method  PackageInstallationPlugin|null      search($objectID)
 * @property    PackageInstallationPlugin[] $objects
 */
class PackageInstallationPluginList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = PackageInstallationPlugin::class;
}
