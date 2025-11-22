<?php

namespace wcf\system\cache\builder;

use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\WCF;

/**
 * Caches applications.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class ApplicationCacheBuilder extends AbstractCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters)
    {
        $data = [
            'abbreviation' => [],
            'application' => [],
            'rootApplication' => null,
            'sortedPaths' => [],
        ];

        $sql = "SELECT *
                FROM   wcf" . WCF_N . "_application";
        $statement = WCF::getDB()->prepareUnmanaged($sql);
        $statement->execute();
        $applications = $statement->fetchObjects(Application::class);

        foreach ($applications as $application) {
            $data['application'][$application->packageID] = $application;
            $data['sortedPaths'][$application->packageID] = $application->domainPath;
        }

        \uasort($data['sortedPaths'], static fn($a, $b) => \mb_strlen($b) - \mb_strlen($a));
        $data['rootApplication'] = $this->getRootApplication($data['sortedPaths']);

        if ($data['rootApplication'] !== null) {
            $data['sortedPaths'] = $this->stripCommonPath($data['sortedPaths'], $data['rootApplication']);
        }

        $sql = "SELECT packageID, package
                FROM   wcf" . WCF_N . "_package
                WHERE  isApplication = ?";
        $statement = WCF::getDB()->prepareUnmanaged($sql);
        $statement->execute([1]);
        $packages = $statement->fetchMap('packageID', 'package');
        foreach ($packages as $packageID => $package) {
            $data['abbreviation'][Package::getAbbreviation($package)] = $packageID;
        }

        return $data;
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
    private function getRootApplication(array $sortedPaths): ?int
    {
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
