<?php

namespace wcf\data\package;

use wcf\system\cache\builder\PackageCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the package cache.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageCache extends SingletonFactory
{
    /**
     * list of cached packages
     * @var mixed[][]
     */
    protected $packages = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->packages = PackageCacheBuilder::getInstance()->getData();
    }

    /**
     * Returns a specific package.
     *
     * @param int $packageID
     * @return  Package|null
     */
    public function getPackage($packageID)
    {
        return $this->packages['packages'][$packageID] ?? null;
    }

    /**
     * Returns the id of a specific package or 'null' if not found.
     *
     * @param string $package
     * @return  string|null
     */
    public function getPackageID($package)
    {
        return $this->packages['packageIDs'][$package] ?? null;
    }

    /**
     * Returns all packages.
     *
     * @return  Package[]
     */
    public function getPackages()
    {
        return $this->packages['packages'];
    }

    /**
     * Returns a specific package.
     *
     * @param string $package
     * @return  Package
     */
    public function getPackageByIdentifier($package)
    {
        $packageID = $this->getPackageID($package);
        if ($packageID === null) {
            return null;
        }

        return $this->getPackage($packageID);
    }
}
