<?php

namespace wcf\acp\page;

use GuzzleHttp\Exception\ConnectException;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\acp\form\LicenseEditForm;
use wcf\data\package\Package;
use wcf\data\package\update\PackageUpdate;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\page\AbstractPage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\NamedUserException;
use wcf\system\package\license\exception\ParsingFailed;
use wcf\system\package\license\LicenseApi;
use wcf\system\package\license\LicenseData;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Lists the licensed products and offers to install them.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class LicensePage extends AbstractPage
{
    public $activeMenuItem = 'wcf.acp.menu.link.package.license';

    public $neededPermissions = ['admin.configuration.package.canInstallPackage'];

    private LicenseData $licenseData;

    private array $availablePackages = [];

    private array $installedPackages;

    private array $installablePackages = [];

    private array $packageUpdates = [];

    private array $requiresLicenseExtension = [];

    private const CURRENT_MAJOR = '6.0';

    public function readData()
    {
        parent::readData();

        if (!LicenseApi::hasLicenseCredentials()) {
            return new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(
                    LicenseEditForm::class,
                    [
                        'url' => LinkHandler::getInstance()->getControllerLink(LicensePage::class),
                    ],
                ),
            );
        }

        PackageUpdateDispatcher::getInstance()->refreshPackageDatabase();

        $licenseApi = new LicenseApi();
        try {
            $licenseData = $licenseApi->getUpToDateLicenseData();
        } catch (ConnectException | ClientExceptionInterface) {
            return new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(
                    LicenseEditForm::class,
                    [
                        'failedValidation' => 1,
                        'url' => LinkHandler::getInstance()->getControllerLink(LicensePage::class),
                    ],
                ),
            );
        } catch (ParsingFailed $e) {
            if (\ENABLE_DEBUG_MODE && \ENABLE_DEVELOPER_TOOLS) {
                throw $e;
            }

            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
                'wcf.acp.license.error.parsingFailed',
                [
                    'licenseData' => $licenseApi->readFromFile(),
                ]
            ));
        }

        $this->licenseData = $licenseData;

        $identifiers = \array_merge(
            \array_keys($this->licenseData->woltlab),
            \array_keys($this->licenseData->pluginstore)
        );
        $this->installedPackages = $this->getInstalledPackages($identifiers);

        $identifiers = \array_diff($identifiers, \array_keys($this->installedPackages));
        $identifiers = $this->removeUnknownPackages($identifiers);

        if ($identifiers !== []) {
            $this->installablePackages = $this->getInstallablePackages($identifiers);
            $this->packageUpdates = $this->getPackageUpdates($this->installablePackages);
        }

        foreach (['woltlab', 'pluginstore'] as $type) {
            $this->availablePackages[$type] = \array_filter(
                $this->licenseData->{$type},
                function (string $package) {
                    if (isset($this->installedPackages[$package])) {
                        return true;
                    }

                    return isset($this->installablePackages[$package]);
                },
                \ARRAY_FILTER_USE_KEY
            );

            \uksort($this->availablePackages[$type], function ($packageA, $packageB) {
                $a = $this->installedPackages[$packageA] ?? $this->packageUpdates[$packageA];
                $b = $this->installedPackages[$packageB] ?? $this->packageUpdates[$packageB];

                $aName = \mb_strtolower($a->getName());
                $bName = \mb_strtolower($b->getName());

                return ($b->isApplication <=> $a->isApplication) ?: ($aName <=> $bName);
            });
        }

        foreach ($this->availablePackages['woltlab'] as $identifier => $accessibleVersion) {
            if ($accessibleVersion === '*') {
                continue;
            }

            if ($accessibleVersion === '0.0') {
                $this->requiresLicenseExtension[$identifier] = 'purchase';
            } elseif (\version_compare($accessibleVersion, self::CURRENT_MAJOR, '<')) {
                $this->requiresLicenseExtension[$identifier] = $accessibleVersion;
            }
        }
    }

    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'licenseData' => $this->licenseData,
            'availablePackages' => $this->availablePackages,
            'installedPackages' => $this->installedPackages,
            'installablePackages' => $this->installablePackages,
            'packageUpdates' => $this->packageUpdates,
            'requiresLicenseExtension' => $this->requiresLicenseExtension,
        ]);
    }

    /**
     * @return array<string, Package>
     */
    private function getInstalledPackages(array $identifiers): array
    {
        if ($identifiers === []) {
            return [];
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package IN (?)", [$identifiers]);

        $sql = "SELECT  *
                FROM    wcf1_package
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        $packages = [];
        while ($package = $statement->fetchObject(Package::class)) {
            $packages[$package->package] = $package;
        }

        return $packages;
    }

    private function removeUnknownPackages(array $identifiers): array
    {
        if ($identifiers === []) {
            return [];
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package IN (?)", [$identifiers]);
        $sql = "SELECT  package
                FROM    wcf1_package_update
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return array<string, PackageUpdate>
     */
    private function getPackageUpdates(array $packages): array
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("package IN (?)", [\array_keys($packages)]);
        $sql = "SELECT  *
                FROM    wcf1_package_update
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        $packageUpdates = [];
        while ($packageUpdate = $statement->fetchObject(PackageUpdate::class)) {
            $packageUpdates[$packageUpdate->package] = $packageUpdate;
        }

        return $packageUpdates;
    }

    // Stolen from PackageUpdateAction::search() and slightly modified.
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

        if ($packageUpdates === []) {
            return [];
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

        if ($availablePackages === []) {
            return [];
        }

        // Retrieve the actual version numbers that match the package update ids.
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("packageUpdateVersionID IN (?)", [\array_values($availablePackages)]);
        $sql = "SELECT  packageUpdateVersionID, packageVersion
                FROM    wcf1_package_update_version
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $packageVersions = $statement->fetchMap('packageUpdateVersionID', 'packageVersion');

        return \array_map(fn ($id) => $packageVersions[$id], $availablePackages);
    }

    /**
     * Validates dependencies and exclusions of a package,
     * optionally limited by a minimum version number.
     *
     * Stolen from PackageUpdateAction::canInstall()
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

        // Mark WoltLab packages to be always accessible in order to include
        // them in the dynamically generated list.
        $woltlabUpdateServerID = PackageUpdateServer::getWoltLabUpdateServer()->packageUpdateServerID;

        // filter by version
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add(
            "puv.packageUpdateID IN (?)",
            [$packageUpdateID]
        );
        $sql = "SELECT      pu.package, pu.packageUpdateServerID, puv.packageUpdateVersionID,
                            puv.packageUpdateID, puv.packageVersion, puv.isAccessible
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

            if ($row['packageUpdateServerID'] === $woltlabUpdateServerID || $row['isAccessible']) {
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
}
