<?php

namespace wcf\system\cache\builder;

use wcf\data\package\PackageList;

/**
 * Caches all installed packages.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $data = [
            'packages' => [],
            'packageIDs' => [],
        ];

        $packageList = new PackageList();
        $packageList->readObjects();

        foreach ($packageList as $package) {
            $data['packages'][$package->packageID] = $package;
            $data['packageIDs'][$package->package] = $package->packageID;
        }

        return $data;
    }
}
