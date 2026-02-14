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

        $sortedPaths = [];
        foreach ($applications as $application) {
            $sortedPaths[$application->packageID] = $application->domainPath;
        }

        \uasort($sortedPaths, static fn($a, $b) => \mb_strlen($b) - \mb_strlen($a));
        $rootApplicationID = $this->getRootApplicationID($sortedPaths);

        if ($rootApplicationID !== null) {
            $sortedPaths = $this->stripCommonPath($sortedPaths, $rootApplicationID);
        }

        return new ApplicationCacheData(
            $applications,
            $abbreviation,
            $rootApplicationID,
            $sortedPaths,
        );
    }

    /**
     * @param array<int, string> $sortedPaths
     * @return array<int, string>
     * @since 6.2
     */
    private function stripCommonPath(array $sortedPaths, int $rootApplication): array
    {
        $length = \mb_strlen($sortedPaths[$rootApplication]);

        return \array_map(
            static fn($path) => \mb_substr($path, $length),
            $sortedPaths
        );
    }

    /**
     * @param array<int, string> $sortedPaths
     * @since 6.2
     */
    private function getRootApplicationID(array $sortedPaths): ?int
    {
        // There are no applications during the setup.
        if ($sortedPaths === []) {
            return null;
        }

        $candidate = \array_key_last($sortedPaths);
        $shortestPath = $sortedPaths[$candidate];

        foreach ($sortedPaths as $path) {
            if (!\str_starts_with($path, $shortestPath)) {
                return null;
            }
        }

        return $candidate;
    }
}
