<?php

namespace wcf\system\cache\eager;

use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\cache\eager\data\ApplicationCacheData;
use wcf\system\WCF;

/**
 * Eager cache for applications.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractEagerCache<ApplicationCacheData>
 */
final class ApplicationCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): ApplicationCacheData
    {
        $sql = "SELECT *
                FROM   wcf" . WCF_N . "_application";
        $statement = WCF::getDB()->prepareUnmanaged($sql);
        $statement->execute();
        $applications = $statement->fetchObjects(Application::class, 'packageID');


        $sql = "SELECT packageID, package
                FROM   wcf" . WCF_N . "_package
                WHERE  isApplication = ?";
        $statement = WCF::getDB()->prepareUnmanaged($sql);
        $statement->execute([1]);
        $packages = $statement->fetchMap('packageID', 'package');

        $abbreviation = [];
        foreach ($packages as $packageID => $package) {
            $abbreviation[Package::getAbbreviation($package)] = $packageID;
        }

        return new ApplicationCacheData(
            $applications,
            $abbreviation,
        );
    }
}
