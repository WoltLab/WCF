<?php

namespace wcf\data\package\installation\plugin;

use wcf\data\DatabaseObject;
use wcf\system\package\plugin\IPackageInstallationPlugin;

/**
 * Represents a package installation plugin.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   string $pluginName unique name and textual identifier of the package installation plugin
 * @property-read   int|null $packageID  id of the package the which delivers the package installation plugin
 * @property-read   int $priority   priority in which the package installation plugins are installed, `1` for Core package installation plugins (executed first) and `0` for other package installation plugins
 * @property-read   string $className  name of the PHP class implementing `wcf\system\package\plugin\IPackageInstallationPlugin` handling installing and uninstalling handled data
 */
class PackageInstallationPlugin extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'pluginName';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexIsIdentity = false;

    /**
     * Returns the default file name containing the instructions or `null` if no default
     * file name is supported.
     *
     * @return  null|string
     * @see     IPackageInstallationPlugin::getDefaultFilename()
     *
     * @since   5.2
     */
    public function getDefaultFilename()
    {
        return $this->className::getDefaultFilename();
    }
}
