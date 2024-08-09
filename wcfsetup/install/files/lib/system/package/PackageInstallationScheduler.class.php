<?php

namespace wcf\system\package;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\package\update\PackageUpdate;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\io\HttpFactory;
use wcf\system\package\exception\IncoherentUpdatePath;
use wcf\system\package\exception\UnknownUpdatePath;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Contains business logic related to preparation of package installations.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class PackageInstallationScheduler
{
    /**
     * stack of package installations / updates
     * @var array
     */
    protected $packageInstallationStack = [];

    /**
     * list of package update servers
     * @var PackageUpdateServer[]
     */
    protected $packageUpdateServers = [];

    /**
     * list of packages to update or install
     * @var array
     */
    protected $selectedPackages = [];

    /**
     * virtual package versions
     * @var array
     */
    protected $virtualPackageVersions = [];

    /**
     * Creates a new instance of PackageInstallationScheduler
     *
     * @param string[] $selectedPackages
     */
    public function __construct(array $selectedPackages)
    {
        $this->selectedPackages = $selectedPackages;
        $this->packageUpdateServers = PackageUpdateServer::getActiveUpdateServers();
    }

    /**
     * Builds the stack of package installations / updates.
     *
     * @param bool $validateInstallInstructions
     */
    public function buildPackageInstallationStack($validateInstallInstructions = false)
    {
        foreach ($this->selectedPackages as $package => $version) {
            $this->tryToInstallPackage($package, $version, true, $validateInstallInstructions);
        }
    }

    /**
     * Tries to install a new package. Checks the virtual package version list.
     *
     * @param string $package package identifier
     * @param string $minversion preferred package version
     * @param bool $installOldVersion true, if you want to install the package in the given minversion and not in the newest version
     * @param bool $validateInstallInstructions
     */
    protected function tryToInstallPackage(
        $package,
        $minversion = '',
        $installOldVersion = false,
        $validateInstallInstructions = false
    ) {
        // check virtual package version
        if (isset($this->virtualPackageVersions[$package])) {
            if (
                !empty($minversion) && Package::compareVersion(
                    $this->virtualPackageVersions[$package],
                    $minversion,
                    '<'
                )
            ) {
                $stackPosition = -1;
                // remove installation of older version
                foreach ($this->packageInstallationStack as $key => $value) {
                    if ($value['package'] == $package) {
                        $stackPosition = $key;
                        break;
                    }
                }

                // install newer version
                $this->installPackage(
                    $package,
                    ($installOldVersion ? $minversion : ''),
                    $stackPosition,
                    $validateInstallInstructions
                );
            }
        } else {
            // check if package is already installed
            $packageID = PackageCache::getInstance()->getPackageID($package);
            if ($packageID === null) {
                // package is missing -> install
                $this->installPackage(
                    $package,
                    ($installOldVersion ? $minversion : ''),
                    -1,
                    $validateInstallInstructions
                );
            } else {
                $package = PackageCache::getInstance()->getPackage($packageID);
                if (!empty($minversion) && Package::compareVersion($package->packageVersion, $minversion, '<')) {
                    $this->updatePackage($packageID, ($installOldVersion ? $minversion : ''));
                }
            }
        }
    }

    /**
     * Installs a new package.
     *
     * @param string $package package identifier
     * @param string $version package version
     * @param int $stackPosition
     * @param bool $validateInstallInstructions
     */
    protected function installPackage(
        $package,
        $version = '',
        $stackPosition = -1,
        $validateInstallInstructions = false
    ) {
        // get package update versions
        $packageUpdateVersions = PackageUpdateDispatcher::getInstance()->getPackageUpdateVersions($package, $version);

        // resolve requirements
        $this->resolveRequirements($packageUpdateVersions[0]['packageUpdateVersionID']);

        // download package
        $download = $this->downloadPackage($package, $packageUpdateVersions, $validateInstallInstructions);

        // add to stack
        $data = [
            'packageName' => $packageUpdateVersions[0]['packageName'],
            'packageVersion' => $packageUpdateVersions[0]['packageVersion'],
            'package' => $package,
            'packageID' => 0,
            'archive' => $download,
            'action' => 'install',
        ];
        if ($stackPosition == -1) {
            $this->packageInstallationStack[] = $data;
        } else {
            $this->packageInstallationStack[$stackPosition] = $data;
        }

        // update virtual versions
        $this->virtualPackageVersions[$package] = $packageUpdateVersions[0]['packageVersion'];
    }

    /**
     * Resolves the package requirements of an package update.
     * Starts the installation or update to higher version of required packages.
     *
     * @param int $packageUpdateVersionID
     */
    protected function resolveRequirements($packageUpdateVersionID)
    {
        // resolve requirements
        $requiredPackages = [];
        $requirementsCache = [];
        $sql = "SELECT  *
                FROM    wcf1_package_update_requirement
                WHERE   packageUpdateVersionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$packageUpdateVersionID]);
        while ($row = $statement->fetchArray()) {
            $requiredPackages[] = $row['package'];
            $requirementsCache[] = $row;
        }

        if (!empty($requiredPackages)) {
            // find installed packages
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("package IN (?)", [$requiredPackages]);

            $installedPackages = [];
            $sql = "SELECT  packageID,
                            package,
                            packageVersion
                    FROM    wcf1_package
                    {$conditions}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            while ($row = $statement->fetchArray()) {
                if (!isset($installedPackages[$row['package']])) {
                    $installedPackages[$row['package']] = [];
                }
                $installedPackages[$row['package']][$row['packageID']] = ($this->virtualPackageVersions[$row['packageID']] ?? $row['packageVersion']);
            }

            // check installed / missing packages
            foreach ($requirementsCache as $row) {
                if (isset($installedPackages[$row['package']])) {
                    // package already installed -> check version
                    // sort multiple instances by version number
                    \uasort($installedPackages[$row['package']], Package::compareVersion(...));

                    $packageID = 0;
                    foreach ($installedPackages[$row['package']] as $packageID => $packageVersion) {
                        if (
                            empty($row['minversion']) || Package::compareVersion(
                                $row['minversion'],
                                $packageVersion,
                                '<='
                            )
                        ) {
                            continue 2;
                        }
                    }

                    // package version too low -> update necessary
                    $this->updatePackage($packageID, $row['minversion']);
                } else {
                    $this->tryToInstallPackage($row['package'], $row['minversion']);
                }
            }
        }
    }

    /**
     * Tries to download a package from available update servers.
     *
     * @param string $package package identifier
     * @param array $packageUpdateVersions package update versions
     * @param bool $validateInstallInstructions
     * @return  string      tmp filename of a downloaded package
     * @throws  PackageUpdateUnauthorizedException
     * @throws  SystemException
     */
    protected function downloadPackage($package, $packageUpdateVersions, $validateInstallInstructions = false)
    {
        // get download from cache
        if ($filename = $this->getCachedDownload($package, $packageUpdateVersions[0]['packageVersion'])) {
            return $filename;
        }

        // download file
        foreach ($packageUpdateVersions as $packageUpdateVersion) {
            // get auth data
            $authData = $this->getAuthData($packageUpdateVersion);
            $options = [];
            if (!empty($authData)) {
                $options[RequestOptions::AUTH] = [
                    $authData['username'],
                    $authData['password'],
                ];
            }
            $client = HttpFactory::makeClient($options);

            if ($packageUpdateVersion['filename']) {
                $request = new Request(
                    'POST',
                    $packageUpdateVersion['filename'],
                    ['Content-Type' => 'application/x-www-form-urlencoded'],
                    \http_build_query(
                        ['apiVersion' => PackageUpdate::API_VERSION],
                        '',
                        '&',
                        \PHP_QUERY_RFC1738
                    )
                );
            } else {
                $parameters = [
                    'apiVersion' => PackageUpdate::API_VERSION,
                    'packageName' => $packageUpdateVersion['package'],
                    'packageVersion' => $packageUpdateVersion['packageVersion'],
                ];
                if ($this->packageUpdateServers[$packageUpdateVersion['packageUpdateServerID']]->isTrustedServer()) {
                    $parameters['instanceId'] = \hash_hmac('sha256', 'api.woltlab.com', \WCF_UUID);
                }

                $request = new Request(
                    'POST',
                    $this->packageUpdateServers[$packageUpdateVersion['packageUpdateServerID']]->getDownloadURL(),
                    ['Content-Type' => 'application/x-www-form-urlencoded'],
                    \http_build_query(
                        $parameters,
                        '',
                        '&',
                        \PHP_QUERY_RFC1738
                    )
                );
            }

            try {
                $response = $client->send($request);
            } catch (ClientException $e) {
                throw new PackageUpdateUnauthorizedException(
                    $e->getResponse()->getStatusCode(),
                    $e->getResponse()->getHeaders(),
                    $e->getResponse()->getBody(),
                    $this->packageUpdateServers[$packageUpdateVersion['packageUpdateServerID']],
                    $packageUpdateVersion
                );
            }

            // check response
            if ($response->getStatusCode() !== 200) {
                throw new SystemException(WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.package.error.downloadFailed',
                    ['__downloadPackage' => $package]
                ) . ' (' . $response->getBody() . ')');
            }

            // write content to tmp file
            $filename = FileUtil::getTemporaryFilename('package_');
            \file_put_contents($filename, $response->getBody());
            unset($response);

            // test package
            $archive = new PackageArchive($filename);
            $archive->openArchive();

            // check install instructions
            if ($validateInstallInstructions) {
                $installInstructions = $archive->getInstallInstructions();
                if (empty($installInstructions)) {
                    throw new SystemException("Package '" . $archive->getLocalizedPackageInfo('packageName') . "' (" . $archive->getPackageInfo('name') . ") does not contain valid installation instructions.");
                }
            }

            $archive->getTar()->close();

            // cache download in session
            PackageUpdateDispatcher::getInstance()->cacheDownload(
                $package,
                $packageUpdateVersion['packageVersion'],
                $filename
            );

            return $filename;
        }

        return false;
    }

    /**
     * Returns a list of excluded packages.
     *
     * @return  array
     */
    public function getExcludedPackages()
    {
        $excludedPackages = [];

        if (!empty($this->packageInstallationStack)) {
            $packageInstallations = [];
            $packageIdentifier = [];
            foreach ($this->packageInstallationStack as $packageInstallation) {
                $packageInstallation['newVersion'] = ($packageInstallation['action'] == 'update' ? $packageInstallation['toVersion'] : $packageInstallation['packageVersion']);
                $packageInstallations[] = $packageInstallation;
                $packageIdentifier[] = $packageInstallation['package'];
            }

            // check exclusions of the new packages
            // get package update ids
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("package IN (?)", [$packageIdentifier]);

            $sql = "SELECT  packageUpdateID,
                            package
                    FROM    wcf1_package_update
                    {$conditions}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            while ($row = $statement->fetchArray()) {
                foreach ($packageInstallations as $key => $packageInstallation) {
                    if ($packageInstallation['package'] == $row['package']) {
                        $packageInstallations[$key]['packageUpdateID'] = $row['packageUpdateID'];
                    }
                }
            }

            // get exclusions of the new packages
            // build conditions
            $conditions = '';
            $statementParameters = [];
            foreach ($packageInstallations as $packageInstallation) {
                if (!empty($conditions)) {
                    $conditions .= ' OR ';
                }
                $conditions .= "(packageUpdateID = ? AND packageVersion = ?)";
                $statementParameters[] = $packageInstallation['packageUpdateID'];
                $statementParameters[] = $packageInstallation['newVersion'];
            }

            $sql = "SELECT      package.*,
                                package_update_exclusion.*,
                                package_update.packageUpdateID,
                                package_update.package
                    FROM        wcf1_package_update_exclusion package_update_exclusion
                    LEFT JOIN   wcf1_package_update_version package_update_version
                    ON          package_update_version.packageUpdateVersionID = package_update_exclusion.packageUpdateVersionID
                    LEFT JOIN   wcf1_package_update package_update
                    ON          package_update.packageUpdateID = package_update_version.packageUpdateID
                    LEFT JOIN   wcf1_package package
                    ON          package.package = package_update_exclusion.excludedPackage
                    WHERE       package_update_exclusion.packageUpdateVersionID IN (
                                    SELECT  packageUpdateVersionID
                                    FROM    wcf1_package_update_version
                                    WHERE   {$conditions}
                                )
                            AND package.package IS NOT NULL";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($statementParameters);
            while ($row = $statement->fetchArray()) {
                foreach ($packageInstallations as $key => $packageInstallation) {
                    if ($packageInstallation['package'] == $row['package']) {
                        if (!isset($packageInstallations[$key]['excludedPackages'])) {
                            $packageInstallations[$key]['excludedPackages'] = [];
                        }
                        $packageInstallations[$key]['excludedPackages'][$row['excludedPackage']] = [
                            'package' => $row['excludedPackage'],
                            'version' => $row['excludedPackageVersion'],
                        ];

                        // check version
                        if (
                            $row['excludedPackageVersion'] !== '*'
                            && Package::compareVersion($row['packageVersion'], $row['excludedPackageVersion'], '<')
                        ) {
                            continue;
                        }

                        $excludedPackages[] = [
                            'package' => $row['package'],
                            'packageName' => $packageInstallations[$key]['packageName'],
                            'packageVersion' => $packageInstallations[$key]['newVersion'],
                            'action' => $packageInstallations[$key]['action'],
                            'conflict' => 'newPackageExcludesExistingPackage',
                            'existingPackage' => $row['excludedPackage'],
                            'existingPackageName' => WCF::getLanguage()->get($row['packageName']),
                            'existingPackageVersion' => $row['packageVersion'],
                        ];
                    }
                }
            }

            // check excluded packages of the existing packages
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("excludedPackage IN (?)", [$packageIdentifier]);

            $sql = "SELECT      package.*,
                                package_exclusion.*
                    FROM        wcf1_package_exclusion package_exclusion
                    LEFT JOIN   wcf1_package package
                    ON          package.packageID = package_exclusion.packageID
                    {$conditions}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());
            while ($row = $statement->fetchArray()) {
                foreach ($packageInstallations as $packageInstallation) {
                    if ($packageInstallation['package'] == $row['excludedPackage']) {
                        if (!empty($row['excludedPackageVersion'])) {
                            // check version
                            if (
                                $row['excludedPackageVersion'] !== "*"
                                && Package::compareVersion(
                                    $packageInstallation['newVersion'],
                                    $row['excludedPackageVersion'],
                                    '<'
                                )
                            ) {
                                continue;
                            }

                            // search exclusing package in stack
                            foreach ($packageInstallations as $packageUpdate) {
                                if ($packageUpdate['packageID'] == $row['packageID']) {
                                    // check new exclusions
                                    if (
                                        !isset($packageUpdate['excludedPackages']) || !isset($packageUpdate['excludedPackages'][$row['excludedPackage']]) || (!empty($packageUpdate['excludedPackages'][$row['excludedPackage']]['version']) && Package::compareVersion(
                                            $packageInstallation['newVersion'],
                                            $packageUpdate['excludedPackages'][$row['excludedPackage']]['version'],
                                            '<'
                                        ))
                                    ) {
                                        continue 2;
                                    }
                                }
                            }
                        }

                        $excludedPackages[] = [
                            'package' => $row['excludedPackage'],
                            'packageName' => $packageInstallation['packageName'],
                            'packageVersion' => $packageInstallation['newVersion'],
                            'action' => $packageInstallation['action'],
                            'conflict' => 'existingPackageExcludesNewPackage',
                            'existingPackage' => $row['package'],
                            'existingPackageName' => WCF::getLanguage()->get($row['packageName']),
                            'existingPackageVersion' => $row['packageVersion'],
                        ];
                    }
                }
            }
        }

        return $excludedPackages;
    }

    /**
     * Returns the stack of package installations.
     *
     * @return  array
     */
    public function getPackageInstallationStack()
    {
        return $this->packageInstallationStack;
    }

    /**
     * Updates an existing package.
     *
     * @param int $packageID
     * @param string $version
     */
    protected function updatePackage($packageID, $version)
    {
        // get package info
        $package = PackageCache::getInstance()->getPackage($packageID);

        // get current package version
        $packageVersion = $package->packageVersion;
        if (isset($this->virtualPackageVersions[$packageID])) {
            $packageVersion = $this->virtualPackageVersions[$packageID];
            // check virtual package version
            if (Package::compareVersion($packageVersion, $version, '>=')) {
                // virtual package version is greater than requested version
                // skip package update
                return;
            }
        }

        // get highest version of the required major release
        if (\preg_match('/(\d+\.\d+\.)/', $version, $match)) {
            $sql = "SELECT  DISTINCT packageVersion
                    FROM    wcf1_package_update_version
                    WHERE   packageUpdateID IN (
                                SELECT  packageUpdateID
                                FROM    wcf1_package_update
                                WHERE   package = ?
                            )
                        AND packageVersion LIKE ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $package->package,
                $match[1] . '%',
            ]);
            $packageVersions = $statement->fetchAll(\PDO::FETCH_COLUMN);

            $count = \count($packageVersions);
            if ($count > 1) {
                // sort by version number
                \usort($packageVersions, Package::compareVersion(...));

                // get highest version
                $version = \array_pop($packageVersions);
            } elseif ($count === 1 && $version !== $packageVersions[0]) {
                // This may happen if there is a compatible but newer version of the required
                // version, that also happens to be the only available version. For example,
                // "5.2.0" is requested but there is only "5.2.0 pl 1".
                $version = $packageVersions[0];
            }
        }

        // get all fromversion
        $fromversions = [];
        $sql = "SELECT      puv.packageVersion,
                            puf.fromversion
                FROM        wcf1_package_update_fromversion puf
                LEFT JOIN   wcf1_package_update_version puv
                ON          puv.packageUpdateVersionID = puf.packageUpdateVersionID
                WHERE       puf.packageUpdateVersionID IN (
                                SELECT  packageUpdateVersionID
                                FROM    wcf1_package_update_version
                                WHERE   packageUpdateID IN (
                                            SELECT  packageUpdateID
                                            FROM    wcf1_package_update
                                            WHERE   package = ?
                                        )
                            )";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$package->package]);
        while ($row = $statement->fetchArray()) {
            if (!isset($fromversions[$row['packageVersion']])) {
                $fromversions[$row['packageVersion']] = [];
            }
            $fromversions[$row['packageVersion']][$row['fromversion']] = $row['fromversion'];
        }

        // sort by version number
        \uksort($fromversions, Package::compareVersion(...));

        // find shortest update thread
        try {
            $updateThread = $this->findShortestUpdateThread($package->package, $fromversions, $packageVersion, $version);
        } catch (IncoherentUpdatePath | UnknownUpdatePath $e) {
            throw new NamedUserException($e->getMessage(), 0, $e);
        }

        // process update thread
        foreach ($updateThread as $fromversion => $toVersion) {
            $packageUpdateVersions = PackageUpdateDispatcher::getInstance()->getPackageUpdateVersions(
                $package->package,
                $toVersion
            );

            // resolve requirements
            $this->resolveRequirements($packageUpdateVersions[0]['packageUpdateVersionID']);

            // download package
            $download = $this->downloadPackage($package->package, $packageUpdateVersions);

            // add to stack
            $this->packageInstallationStack[] = [
                'packageName' => $package->getName(),
                'fromversion' => $fromversion,
                'toVersion' => $toVersion,
                'package' => $package->package,
                'packageID' => $packageID,
                'archive' => $download,
                'action' => 'update',
            ];

            // update virtual versions
            $this->virtualPackageVersions[$packageID] = $toVersion;
        }
    }

    /**
     * Determines intermediate update steps using a backtracking algorithm in case there is no direct upgrade possible.
     *
     * @param string $package package identifier
     * @param array $fromversions list of all fromversions
     * @param string $currentVersion current package version
     * @param string $newVersion new package version
     * @return  array       list of update steps (old version => new version, old version => new version, ...)
     * @throws  SystemException
     */
    protected function findShortestUpdateThread($package, $fromversions, $currentVersion, $newVersion)
    {
        if (!isset($fromversions[$newVersion])) {
            throw new UnknownUpdatePath($package, $currentVersion, $newVersion);
        }

        // find direct update
        foreach ($fromversions[$newVersion] as $fromversion) {
            if (Package::checkFromversion($currentVersion, $fromversion)) {
                return [$currentVersion => $newVersion];
            }
        }

        // find intermediate update
        $packageVersions = \array_keys($fromversions);
        $updateThreadList = [];
        foreach ($fromversions[$newVersion] as $fromversion) {
            $innerUpdateThreadList = [];
            // find matching package versions
            foreach ($packageVersions as $packageVersion) {
                if (
                    Package::checkFromversion($packageVersion, $fromversion) && Package::compareVersion(
                        $packageVersion,
                        $currentVersion,
                        '>'
                    ) && Package::compareVersion($packageVersion, $newVersion, '<')
                ) {
                    try {
                        $innerUpdateThreadList[] = $this->findShortestUpdateThread(
                            $package,
                            $fromversions,
                            $currentVersion,
                            $packageVersion
                        ) + [$packageVersion => $newVersion];
                    } catch (IncoherentUpdatePath $e) {
                        // Ignore issues caused by multiple split update paths
                        // where the first path has incoherent, but other valid
                        // paths exist.
                        continue;
                    }
                }
            }

            if (!empty($innerUpdateThreadList)) {
                // sort by length
                \usort($innerUpdateThreadList, $this->compareUpdateThreadLists(...));

                // add to thread list
                $updateThreadList[] = \array_shift($innerUpdateThreadList);
            }
        }

        if (empty($updateThreadList)) {
            throw new IncoherentUpdatePath($package, $currentVersion, $newVersion);
        }

        // sort by length
        \usort($updateThreadList, $this->compareUpdateThreadLists(...));

        // take shortest
        return \array_shift($updateThreadList);
    }

    /**
     * Compares the length of two updates threads.
     */
    protected function compareUpdateThreadLists(array $updateThreadListA, array $updateThreadListB): int
    {
        return \count($updateThreadListA) <=> \count($updateThreadListB);
    }

    /**
     * Returns the filename of downloads stored in session or null if no stored downloads exist.
     *
     * @param string $package package identifier
     * @param string $version package version
     * @return  string|bool
     */
    protected function getCachedDownload($package, $version)
    {
        $cachedDownloads = WCF::getSession()->getVar('cachedPackageUpdateDownloads');
        if (isset($cachedDownloads[$package . '@' . $version]) && @\file_exists($cachedDownloads[$package . '@' . $version])) {
            return $cachedDownloads[$package . '@' . $version];
        }

        return false;
    }

    /**
     * Returns stored auth data the update server with given data.
     *
     * @param array $data
     * @return  array
     */
    protected function getAuthData(array $data)
    {
        $updateServer = new PackageUpdateServer(null, $data);

        return $updateServer->getAuthData();
    }
}
