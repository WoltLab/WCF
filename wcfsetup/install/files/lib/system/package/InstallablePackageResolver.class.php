<?php

namespace wcf\system\package;

use wcf\data\package\Package;
use wcf\data\package\update\PackageUpdate;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\version\PackageUpdateVersion;
use wcf\data\package\update\ViewablePackageUpdate;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Determines which packages offered by the active package update servers can be installed.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.3
 * @phpstan-type VersionIDs array{accessible: int, existing: int}
 */
final class InstallablePackageResolver
{
    /**
     * Returns the installable packages among the given package updates. Package updates of
     * inactive servers and of installed packages are ignored. When a package is offered by
     * several servers, only one source is returned for it.
     *
     * @param list<int> $packageUpdateIDs
     * @return array<int, ViewablePackageUpdate>
     */
    public function resolve(array $packageUpdateIDs): array
    {
        if ($packageUpdateIDs === []) {
            return [];
        }

        $updateServers = PackageUpdateServer::getActiveUpdateServers();
        if ($updateServers === []) {
            return [];
        }
        $updateServerIDs = \array_keys($updateServers);

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package_update.packageUpdateID IN (?)", [$packageUpdateIDs]);
        $conditions->add("package_update.packageUpdateServerID IN (?)", [$updateServerIDs]);
        $conditions->add("package.packageID IS NULL");

        $sql = "SELECT      package_update.packageUpdateID
                FROM        wcf1_package_update package_update
                LEFT JOIN   wcf1_package package
                ON          package.package = package_update.package
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $packageUpdateIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if ($packageUpdateIDs === []) {
            return [];
        }

        // get installed packages
        $sql = "SELECT  package, packageVersion
                FROM    wcf1_package";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $installedPackages = $statement->fetchMap('package', 'packageVersion');

        // get excluded packages (of installed packages)
        $excludedPackagesOfInstalledPackages = [];
        $sql = "SELECT  excludedPackage, excludedPackageVersion
                FROM    wcf1_package_exclusion";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if (!isset($excludedPackagesOfInstalledPackages[$row['excludedPackage']])) {
                $excludedPackagesOfInstalledPackages[$row['excludedPackage']] = $row['excludedPackageVersion'];
            } elseif (
                $row['excludedPackageVersion'] === '*'
                || Package::compareVersion(
                    $excludedPackagesOfInstalledPackages[$row['excludedPackage']],
                    $row['excludedPackageVersion'],
                    '>'
                )
            ) {
                $excludedPackagesOfInstalledPackages[$row['excludedPackage']] = $row['excludedPackageVersion'];
            }
        }

        $packageUpdates = [];
        foreach ($packageUpdateIDs as $packageUpdateID) {
            $result = $this->canInstall(
                $packageUpdateID,
                null,
                $installedPackages,
                $excludedPackagesOfInstalledPackages,
                $updateServerIDs,
                []
            );
            if (isset($result[$packageUpdateID])) {
                $packageUpdates[$packageUpdateID] = $result[$packageUpdateID];
            }
        }

        if ($packageUpdates === []) {
            return [];
        }

        // remove duplicates by preferring the sources on trusted package servers,
        // then picking the source with the highest accessible version
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("packageUpdateID IN (?)", [\array_keys($packageUpdates)]);
        $sql = "SELECT  packageUpdateID, packageUpdateServerID, package
                FROM    wcf1_package_update
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $possiblePackages = [];
        while ($row = $statement->fetchArray()) {
            $possiblePackages[$row['package']][$row['packageUpdateID']] = $row['packageUpdateServerID'];
        }

        $trustedServerIDs = [];
        foreach ($updateServers as $packageUpdateServer) {
            if ($packageUpdateServer->isTrustedServer() || $packageUpdateServer->isWoltLabStoreServer()) {
                $trustedServerIDs[] = $packageUpdateServer->packageUpdateServerID;
            }
        }

        // remove duplicates when there are both versions from trusted and untrusted servers
        foreach ($possiblePackages as $identifier => $packageSources) {
            $hasTrustedSource = false;
            foreach ($packageSources as $packageUpdateServerID) {
                if (\in_array($packageUpdateServerID, $trustedServerIDs, true)) {
                    $hasTrustedSource = true;
                    break;
                }
            }

            if ($hasTrustedSource) {
                $possiblePackages[$identifier] = \array_filter(
                    $packageSources,
                    static function ($packageUpdateServerID) use ($trustedServerIDs) {
                        return \in_array($packageUpdateServerID, $trustedServerIDs, true);
                    }
                );
            }
        }

        // Keep only the source with the highest accessible version of each package.
        $validPackageUpdateIDs = [];
        foreach ($possiblePackages as $packageSources) {
            if (\count($packageSources) > 1) {
                $packageUpdateVersionIDs = [];
                foreach (\array_keys($packageSources) as $packageUpdateID) {
                    $packageUpdateVersionIDs[] = $packageUpdates[$packageUpdateID]['accessible'];
                }

                $conditions = new PreparedStatementConditionBuilder();
                $conditions->add("packageUpdateVersionID IN (?)", [$packageUpdateVersionIDs]);

                $sql = "SELECT  packageUpdateVersionID, packageUpdateID, packageVersion
                        FROM    wcf1_package_update_version
                        " . $conditions;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditions->getParameters());
                $packageVersions = [];
                while ($row = $statement->fetchArray()) {
                    $packageVersions[$row['packageUpdateVersionID']] = [
                        'packageUpdateID' => $row['packageUpdateID'],
                        'packageVersion' => $row['packageVersion'],
                    ];
                }

                // Sort packages with the highest version ending up on top.
                \uasort($packageVersions, static function ($a, $b) {
                    return Package::compareVersion($b['packageVersion'], $a['packageVersion']);
                });

                \reset($packageVersions);
                $validPackageUpdateIDs[] = \current($packageVersions)['packageUpdateID'];
            } else {
                \reset($packageSources);
                $validPackageUpdateIDs[] = \key($packageSources);
            }
        }

        // filter by package update version ids
        foreach ($packageUpdates as $packageUpdateID => $packageData) {
            if (!\in_array($packageUpdateID, $validPackageUpdateIDs, true)) {
                unset($packageUpdates[$packageUpdateID]);
            }
        }

        return $this->getViewablePackageUpdates($updateServers, $packageUpdates);
    }

    /**
     * Returns the installable packages among the given package identifiers, see `resolve()`.
     *
     * @param list<string> $identifiers
     * @return array<int, ViewablePackageUpdate>
     */
    public function resolveByIdentifiers(array $identifiers): array
    {
        if ($identifiers === []) {
            return [];
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package IN (?)", [$identifiers]);

        $sql = "SELECT  packageUpdateID
                FROM    wcf1_package_update
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        return $this->resolve($statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    /**
     * Validates dependencies and exclusions of a package,
     * optionally limited by a minimum version number.
     *
     * @param array<string, string> $installedPackages
     * @param array<string, string> $excludedPackagesOfInstalledPackages
     * @param list<int> $updateServerIDs
     * @param array<int, true> $visiting
     * @return array<int, VersionIDs>
     */
    private function canInstall(
        int $packageUpdateID,
        ?string $minVersion,
        array $installedPackages,
        array $excludedPackagesOfInstalledPackages,
        array $updateServerIDs,
        array $visiting
    ): array {
        if (isset($visiting[$packageUpdateID])) {
            return [];
        }
        $visiting[$packageUpdateID] = true;

        // get excluded packages
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add(
            "packageUpdateVersionID IN (
                SELECT  packageUpdateVersionID
                FROM    wcf1_package_update_version
                WHERE   packageUpdateID = ?
            )",
            [$packageUpdateID]
        );
        $sql = "SELECT  *
                FROM    wcf1_package_update_exclusion
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $excludedPackages = [];
        while ($row = $statement->fetchArray()) {
            $package = $row['excludedPackage'];
            $packageVersion = $row['excludedPackageVersion'];
            $packageUpdateVersionID = $row['packageUpdateVersionID'];

            if (!isset($excludedPackages[$packageUpdateVersionID][$package])) {
                $excludedPackages[$packageUpdateVersionID][$package] = $packageVersion;
            } elseif (
                Package::compareVersion(
                    $excludedPackages[$packageUpdateVersionID][$package],
                    $packageVersion
                ) === 1
            ) {
                $excludedPackages[$packageUpdateVersionID][$package] = $packageVersion;
            }
        }

        // filter by version
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("puv.packageUpdateID IN (?)", [$packageUpdateID]);
        $sql = "SELECT      pu.package, puv.packageUpdateVersionID, puv.packageUpdateID, puv.packageVersion, puv.isAccessible
                FROM        wcf1_package_update_version puv
                LEFT JOIN   wcf1_package_update pu
                ON          pu.packageUpdateID = puv.packageUpdateID
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $packageVersions = [];
        while ($row = $statement->fetchArray()) {
            $package = $row['package'];
            $packageVersion = $row['packageVersion'];
            $packageUpdateVersionID = $row['packageUpdateVersionID'];

            if ($minVersion !== null && Package::compareVersion($packageVersion, $minVersion) === -1) {
                continue;
            }

            // check excluded packages
            if (isset($excludedPackages[$packageUpdateVersionID])) {
                $isExcluded = false;
                foreach ($excludedPackages[$packageUpdateVersionID] as $excludedPackage => $excludedPackageVersion) {
                    if (
                        isset($installedPackages[$excludedPackage]) && Package::compareVersion(
                            $excludedPackageVersion,
                            $installedPackages[$excludedPackage]
                        ) <= 0
                    ) {
                        // excluded, ignore
                        $isExcluded = true;
                        break;
                    }
                }

                if ($isExcluded) {
                    continue;
                }
            }
            // check excluded packages of installed packages
            if (isset($excludedPackagesOfInstalledPackages[$row['package']])) {
                if (
                    Package::compareVersion(
                        $packageVersion,
                        $excludedPackagesOfInstalledPackages[$row['package']],
                        '>='
                    )
                ) {
                    continue;
                }
            }

            if (!isset($packageVersions[$package])) {
                $packageVersions[$package] = [];
            }

            $packageUpdateID = $row['packageUpdateID'];
            if (!isset($packageVersions[$package][$packageUpdateID])) {
                $packageVersions[$package][$packageUpdateID] = [
                    'accessible' => [],
                    'existing' => [],
                ];
            }

            if ($row['isAccessible'] !== 0) {
                $packageVersions[$package][$packageUpdateID]['accessible'][$row['packageUpdateVersionID']] = $packageVersion;
            }
            $packageVersions[$package][$packageUpdateID]['existing'][$row['packageUpdateVersionID']] = $packageVersion;
        }

        // all found versions are excluded
        if ($packageVersions === []) {
            return [];
        }

        // determine highest versions
        $packageUpdates = [];
        foreach ($packageVersions as $versionData) {
            $accessible = $existing = $versions = [];

            foreach ($versionData as $packageUpdateID => $versionTypes) {
                // ignore inaccessible packages
                if ($versionTypes['accessible'] === []) {
                    continue;
                }

                \uasort($versionTypes['accessible'], [Package::class, 'compareVersion']);
                \uasort($versionTypes['existing'], [Package::class, 'compareVersion']);

                $accessibleVersion = \array_slice($versionTypes['accessible'], -1, 1, true);
                $existingVersion = \array_slice($versionTypes['existing'], -1, 1, true);

                $ak = \key($accessibleVersion);
                $av = \current($accessibleVersion);
                $ek = \key($existingVersion);
                $ev = \current($existingVersion);

                $accessible[$av] = $ak;
                $existing[$ev] = $ek;
                $versions[$ak] = $packageUpdateID;
                $versions[$ek] = $packageUpdateID;
            }

            // ignore packages without accessible versions
            if ($accessible === []) {
                continue;
            }

            \uksort($accessible, [Package::class, 'compareVersion']);
            \uksort($existing, [Package::class, 'compareVersion']);

            $accessible = \array_pop($accessible);
            $existing = \array_pop($existing);
            $packageUpdates[$versions[$accessible]] = [
                'accessible' => $accessible,
                'existing' => $existing,
            ];
        }

        // validate dependencies
        foreach ($packageUpdates as $packageUpdateData) {
            $sql = "SELECT  package, minversion
                    FROM    wcf1_package_update_requirement
                    WHERE   packageUpdateVersionID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$packageUpdateData['accessible']]);
            $requirements = [];
            while ($row = $statement->fetchArray()) {
                $package = $row['package'];
                $minVersion = $row['minversion'];

                if (
                    !isset($installedPackages[$package]) || Package::compareVersion(
                        $installedPackages[$package],
                        $minVersion
                    ) === -1
                ) {
                    $requirements[$package] = $minVersion;
                }
            }

            if ($requirements === []) {
                continue;
            }

            $openRequirements = \array_keys($requirements);

            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("package IN (?)", [\array_keys($requirements)]);
            $conditions->add("packageUpdateServerID IN (?)", [$updateServerIDs]);
            $sql = "SELECT  packageUpdateID, package
                    FROM    wcf1_package_update
                    " . $conditions;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            while ($row = $statement->fetchArray()) {
                if (!\in_array($row['package'], $openRequirements, true)) {
                    // The dependency has already been satisfied by another update server.
                    continue;
                }

                $result = $this->canInstall(
                    $row['packageUpdateID'],
                    $requirements[$row['package']],
                    $installedPackages,
                    $excludedPackagesOfInstalledPackages,
                    $updateServerIDs,
                    $visiting
                );
                if ($result !== []) {
                    $index = \array_search($row['package'], $openRequirements, true);
                    unset($openRequirements[$index]);
                }
            }

            if ($openRequirements !== []) {
                return [];
            }
        }

        return $packageUpdates;
    }

    /**
     * @param array<int, PackageUpdateServer> $updateServers
     * @param array<int, VersionIDs> $updateData
     * @return array<int, ViewablePackageUpdate>
     */
    private function getViewablePackageUpdates(array $updateServers, array $updateData): array
    {
        // get package updates
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("packageUpdateID IN (?)", [\array_keys($updateData)]);

        $sql = "SELECT  *
                FROM    wcf1_package_update
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $packageUpdates = $packageVersionIDs = [];
        while ($packageUpdate = $statement->fetchObject(PackageUpdate::class)) {
            $packageUpdates[$packageUpdate->packageUpdateID] = new ViewablePackageUpdate($packageUpdate);

            // collect package version ids
            $versionIDs = $updateData[$packageUpdate->packageUpdateID];
            $packageVersionIDs[] = $versionIDs['accessible'];
            $packageVersionIDs[] = $versionIDs['existing'];
        }

        // read update versions
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("packageUpdateVersionID IN (?)", [$packageVersionIDs]);

        $sql = "SELECT  packageUpdateVersionID, packageVersion, packageDate, license, licenseURL
                FROM    wcf1_package_update_version
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        /** @var PackageUpdateVersion[] $updateVersions */
        $updateVersions = $statement->fetchObjects(PackageUpdateVersion::class, 'packageUpdateVersionID');

        foreach ($packageUpdates as $packageUpdate) {
            $versionIDs = $updateData[$packageUpdate->packageUpdateID];
            $packageUpdate->setAccessibleVersion($updateVersions[$versionIDs['accessible']]);
            $packageUpdate->setLatestVersion($updateVersions[$versionIDs['existing']]);
            $packageUpdate->setUpdateServer($updateServers[$packageUpdate->packageUpdateServerID]);
        }

        return $packageUpdates;
    }
}
