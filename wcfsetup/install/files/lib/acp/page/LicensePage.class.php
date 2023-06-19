<?php

namespace wcf\acp\page;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use GuzzleHttp\Psr7\Request;
use wcf\data\package\Package;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\page\AbstractPage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\io\HttpFactory;
use wcf\system\WCF;

final class LicensePage extends AbstractPage
{
    // TODO: This should be the actual menu item.
    public $activeMenuItem = 'wcf.acp.menu.link.package';

    // TODO: This untyped array is awful, maybe use a custom object intead and
    // ask Valinor to hydrate it?
    private array $licenseData;

    // TODO: This could be part of the aforementioned custom object.
    private int $licenseNumber;

    private array $installedPackages;

    private array $installablePackages = [];

    public function readData()
    {
        parent::readData();

        // TODO: We actually need to fetch the data from the official package
        // update servers first, because we rely on the human readable names.

        // TODO: We should cache this data for a certain amount of time. There
        // also needs tobe a way to manually trigger a refresh in case of a
        // recent purchase.
        $this->licenseData = $this->fetchLicenseData();

        $identifiers = \array_merge(
            \array_keys($this->licenseData['woltlab']),
            \array_keys($this->licenseData['pluginstore'])
        );
        $this->installedPackages = $this->getInstalledPackages($identifiers);

        $identifiers = \array_diff($identifiers, $this->installedPackages);
        if ($identifiers !== []) {
            $this->installablePackages = $this->getInstallablePackages($identifiers);
        }

        // TODO: This might need to use `getAuthData()` to inject the correct
        // credentials for the WoltLab Cloud.
        $this->licenseNumber = (new PackageUpdateServer(1))->loginUsername;

        // TODO: We need the data from the official package update servers to
        // determine the human readable values for each package and to filter
        // out those that are incompatible with the installed version.
    }

    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'licenseData' => $this->licenseData,
            'licenseNumber' => $this->licenseNumber,
            'installedPackages' => $this->installedPackages,
            'installablePackages' => $this->installablePackages,
        ]);
    }

    private function getInstalledPackages(array $identifiers): array
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package IN (?)", [$identifiers]);

        $sql = "SELECT  package
                FROM    wcf1_package
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        return $statement->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    // TODO: Stolen from PackageUpdateAction::search() and slightly modified.
    private function getInstallablePackages(array $identifiers): array
    {
        $availableUpdateServers = \array_filter(
            PackageUpdateServer::getActiveUpdateServers(),
            static function (PackageUpdateServer $packageUpdateServer) {
                return $packageUpdateServer->isWoltLabUpdateServer() || $packageUpdateServer->isWoltLabStoreServer();
            }
        );

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package_update.packageUpdateServerID IN (?)", [\array_keys($availableUpdateServers)]);
        $conditions->add("package_update.package IN (?)", [$identifiers]);

        // find matching packages
        $sql = "SELECT      package_update.packageUpdateID
                FROM        wcf1_package_update package_update
                LEFT JOIN   wcf1_package package
                ON          package.package = package_update.package
                {$conditions}
                ORDER BY    package_update.packageName ASC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $packageUpdateIDs = $statement->fetchAll(\PDO::FETCH_COLUMN, 0);

        if ($packageUpdateIDs === []) {
            return [];
        }

        // get installed packages
        $sql = "SELECT  package, packageVersion
                FROM    wcf" . WCF_N . "_package";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $installedPackages = $statement->fetchMap('package', 'packageVersion');

        // get excluded packages (of installed packages)
        $excludedPackagesOfInstalledPackages = [];
        $sql = "SELECT  excludedPackage, excludedPackageVersion
                FROM    wcf" . WCF_N . "_package_exclusion";
        $statement = WCF::getDB()->prepareStatement($sql);
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
                $excludedPackagesOfInstalledPackages
            );
            if (isset($result[$packageUpdateID])) {
                $packageUpdates[$packageUpdateID] = $result[$packageUpdateID];
            }
        }

        // remove duplicates by picking either the lowest available version of a package
        // or the version exposed by trusted package servers
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("packageUpdateID IN (?)", [\array_keys($packageUpdates)]);
        $sql = "SELECT  packageUpdateID, packageUpdateServerID, package
                FROM    wcf" . WCF_N . "_package_update
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditions->getParameters());
        $possiblePackages = [];
        while ($row = $statement->fetchArray()) {
            $possiblePackages[$row['package']][$row['packageUpdateID']] = $row['packageUpdateServerID'];
        }

        $trustedServerIDs = [];
        foreach (PackageUpdateServer::getActiveUpdateServers() as $packageUpdateServer) {
            if ($packageUpdateServer->isTrustedServer() || $packageUpdateServer->isWoltLabStoreServer()) {
                $trustedServerIDs[] = $packageUpdateServer->packageUpdateServerID;
            }
        }

        // remove duplicates when there are both versions from trusted and untrusted servers
        foreach ($possiblePackages as $identifier => $packageSources) {
            $hasTrustedSource = false;
            foreach ($packageSources as $packageUpdateServerID) {
                if (\in_array($packageUpdateServerID, $trustedServerIDs)) {
                    $hasTrustedSource = true;
                    break;
                }
            }

            if ($hasTrustedSource) {
                $possiblePackages[$identifier] = \array_filter(
                    $packageSources,
                    static function ($packageUpdateServerID) use ($trustedServerIDs) {
                        return \in_array($packageUpdateServerID, $trustedServerIDs);
                    }
                );
            }
        }

        // Sort by the highest version and return all other sources for the same package.
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
                        FROM    wcf" . WCF_N . "_package_update_version
                        " . $conditions;
                $statement = WCF::getDB()->prepareStatement($sql);
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
            if (!\in_array($packageUpdateID, $validPackageUpdateIDs)) {
                unset($packageUpdates[$packageUpdateID]);
            }
        }

        $availablePackages = [];
        foreach ($possiblePackages as $identifier => $packageData) {
            $packageUpdateID = \key($packageData);
            if (isset($packageUpdates[$packageUpdateID])) {
                $availablePackages[$identifier] = $packageUpdates[$packageUpdateID]['accessible'];
            }
        }

        return $availablePackages;
    }

    /**
     * TODO: Stolen from PackageUpdateAction::canInstall()
     *
     * Validates dependencies and exclusions of a package,
     * optionally limited by a minimum version number.
     *
     * @param int $packageUpdateID
     * @param string|null $minVersion
     * @param string[] $installedPackages
     * @param string[] $excludedPackagesOfInstalledPackages
     * @return      string[][]
     */
    protected function canInstall(
        $packageUpdateID,
        $minVersion,
        array &$installedPackages,
        array &$excludedPackagesOfInstalledPackages
    ) {
        // get excluded packages
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add(
            "packageUpdateVersionID IN (
                SELECT  packageUpdateVersionID
                FROM    wcf" . WCF_N . "_package_update_version
                WHERE   packageUpdateID = ?
            )",
            [$packageUpdateID]
        );
        $sql = "SELECT  *
                FROM    wcf" . WCF_N . "_package_update_exclusion
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
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
                ) == 1
            ) {
                $excludedPackages[$packageUpdateVersionID][$package] = $packageVersion;
            }
        }

        // filter by version
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add(
            "puv.packageUpdateID IN (?)",
            [$packageUpdateID]
        );
        $sql = "SELECT      pu.package, puv.packageUpdateVersionID, puv.packageUpdateID, puv.packageVersion, puv.isAccessible
                FROM        wcf" . WCF_N . "_package_update_version puv
                LEFT JOIN   wcf" . WCF_N . "_package_update pu
                ON          pu.packageUpdateID = puv.packageUpdateID
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditions->getParameters());
        $packageVersions = [];
        while ($row = $statement->fetchArray()) {
            $package = $row['package'];
            $packageVersion = $row['packageVersion'];
            $packageUpdateVersionID = $row['packageUpdateVersionID'];

            if ($minVersion !== null && Package::compareVersion($packageVersion, $minVersion) == -1) {
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
                if (Package::compareVersion(
                    $packageVersion,
                    $excludedPackagesOfInstalledPackages[$row['package']],
                    '>='
                )) {
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

            if ($row['isAccessible']) {
                $packageVersions[$package][$packageUpdateID]['accessible'][$row['packageUpdateVersionID']] = $packageVersion;
            }
            $packageVersions[$package][$packageUpdateID]['existing'][$row['packageUpdateVersionID']] = $packageVersion;
        }

        // all found versions are excluded
        if (empty($packageVersions)) {
            return [];
        }

        // determine highest versions
        $packageUpdates = [];
        foreach ($packageVersions as $versionData) {
            $accessible = $existing = $versions = [];

            foreach ($versionData as $packageUpdateID => $versionTypes) {
                // ignore inaccessible packages
                if (empty($versionTypes['accessible'])) {
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
            if (empty($accessible)) {
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
                    FROM    wcf" . WCF_N . "_package_update_requirement
                    WHERE   packageUpdateVersionID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$packageUpdateData['accessible']]);
            $requirements = [];
            while ($row = $statement->fetchArray()) {
                $package = $row['package'];
                $minVersion = $row['minversion'];

                if (
                    !isset($installedPackages[$package]) || Package::compareVersion(
                        $installedPackages[$package],
                        $minVersion
                    ) == -1
                ) {
                    $requirements[$package] = $minVersion;
                }
            }

            if (empty($requirements)) {
                continue;
            }

            $openRequirements = \array_keys($requirements);

            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("package IN (?)", [\array_keys($requirements)]);
            $sql = "SELECT  packageUpdateID, package
                    FROM    wcf" . WCF_N . "_package_update
                    " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());
            while ($row = $statement->fetchArray()) {
                if (!\in_array($row['package'], $openRequirements)) {
                    // The dependency has already been satisfied by another update server.
                    continue;
                }

                $result = $this->canInstall(
                    $row['packageUpdateID'],
                    $requirements[$row['package']],
                    $installedPackages,
                    $excludedPackagesOfInstalledPackages
                );
                if (!empty($result)) {
                    $index = \array_search($row['package'], $openRequirements);
                    unset($openRequirements[$index]);
                }
            }

            if (!empty($openRequirements)) {
                return [];
            }
        }

        return $packageUpdates;
    }

    // TODO: This code was stol… liberated from "FirstTimeSetupLicenseForm" and
    // should propably be moved into a helper class. We might even want to refresh
    // the data with requests to the package servers to implicitly fetch the
    // latest purchases.
    private function fetchLicenseData(): array|object
    {
        $pus = new PackageUpdateServer(1);

        $request = new Request(
            'POST',
            'https://api.woltlab.com/2.0/customer/license/list.json',
            [
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            \http_build_query([
                'licenseNo' => $pus->loginUsername,
                'serialNo' => $pus->loginPassword,
                'instanceId' => \hash_hmac('sha256', 'api.woltlab.com', \WCF_UUID),
            ], '', '&', \PHP_QUERY_RFC1738)
        );

        $response = HttpFactory::makeClientWithTimeout(5)->send($request);
        return (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->mapper()
            ->map(
                <<<'EOT'
                    array {
                        status: 200,
                        license: array {
                            authCode: string,
                            type: string,
                            expiryDates?: array<string, int>,
                        },
                        pluginstore: array<string, string>,
                        woltlab: array<string, string>,
                    }
                    EOT,
                Source::json($response->getBody())
            );
    }
}
