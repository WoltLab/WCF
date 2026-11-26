<?php

namespace wcf\data\package\update;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\package\InstallablePackageResolver;
use wcf\system\package\PackageInstallationScheduler;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\package\PackageUpdateUnauthorizedException;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Executes package update-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @phpstan-type QueueEntry array{
 *  template: string,
 *  type: 'authorizationRequired',
 * }|array{
 *  template: string,
 *  type: 'conflict',
 * }|array{
 *  queueID: int,
 *  type: 'queue',
 * }
 * @extends AbstractDatabaseObjectAction<PackageUpdate, PackageUpdateEditor>
 */
class PackageUpdateAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = PackageUpdateEditor::class;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['prepareInstallation', 'prepareUpdate', 'search'];

    /**
     * Validates parameters to search for installable packages.
     *
     * @return void
     */
    public function validateSearch()
    {
        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        $this->readString('searchString');
    }

    /**
     * Returns a result list of a search for installable packages.
     *
     * @return mixed[]
     */
    public function search()
    {
        $availableUpdateServers = PackageUpdateServer::getActiveUpdateServers();

        $packageUpdates = [];
        if ($availableUpdateServers !== []) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("package_update.packageUpdateServerID IN (?)", [\array_keys($availableUpdateServers)]);
            $searchString = '%' . WCF::getDB()->escapeLikeValue($this->parameters['searchString']) . '%';
            $conditions->add(
                "(package_update.package LIKE ? OR package_update.packageDescription LIKE ? OR package_update.packageName LIKE ?)",
                [$searchString, $searchString, $searchString]
            );
            $conditions->add("package.packageID IS NULL");

            // find matching packages
            $sql = "SELECT      package_update.packageUpdateID
                    FROM        wcf1_package_update package_update
                    LEFT JOIN   wcf1_package package
                    ON          package.package = package_update.package
                    " . $conditions . "
                    ORDER BY    package_update.packageName ASC";
            $statement = WCF::getDB()->prepare($sql, 1000);
            $statement->execute($conditions->getParameters());
            $packageUpdateIDs = [];
            while ($row = $statement->fetchArray()) {
                $packageUpdateIDs[] = $row['packageUpdateID'];
            }

            $packageUpdates = new InstallablePackageResolver()->resolve($packageUpdateIDs);
        }

        // no matches found
        if ($packageUpdates === []) {
            return [
                'count' => 0,
                'pageCount' => 0,
                'template' => WCF::getTPL()->render('wcf', 'packageSearchResultList', [
                    'officialPackages' => [],
                    'thirdPartySources' => [],
                    'trustedSources' => [],
                ]),
            ];
        }

        return $this->renderResultList($packageUpdates);
    }

    /**
     * Validates dependencies and exclusions of a package,
     * optionally limited by a minimum version number.
     *
     * @param string[] $installedPackages
     * @param string[] $excludedPackagesOfInstalledPackages
     * @return array<int, array{accessible: int, existing: int}>
     * @deprecated 6.3 Use `InstallablePackageResolver::resolve()` instead.
     */
    protected function canInstall(
        int $packageUpdateID,
        ?string $minVersion,
        array &$installedPackages,
        array &$excludedPackagesOfInstalledPackages
    ) {
        $packageUpdate = new InstallablePackageResolver()->resolve([$packageUpdateID])[$packageUpdateID] ?? null;
        if ($packageUpdate === null) {
            return [];
        }

        $accessibleVersion = $packageUpdate->getAccessibleVersion();
        if ($minVersion !== null && Package::compareVersion($accessibleVersion->packageVersion, $minVersion) === -1) {
            return [];
        }

        return [
            $packageUpdateID => [
                'accessible' => $accessibleVersion->packageUpdateVersionID,
                'existing' => $packageUpdate->getLatestVersion()->packageUpdateVersionID,
            ],
        ];
    }

    /**
     * Returns a result list for a previous search.
     *
     * @param PackageUpdateServer[] $updateServers
     * @param mixed[] $updateData
     * @return array{count: int, template: string}
     * @deprecated 6.3 Use `InstallablePackageResolver::resolve()` instead.
     */
    protected function getResultList(array $updateServers, array $updateData)
    {
        return $this->renderResultList(new InstallablePackageResolver()->resolve(\array_keys($updateData)));
    }

    /**
     * @param array<int, ViewablePackageUpdate> $packageUpdates
     * @return array{count: int, template: string}
     */
    private function renderResultList(array $packageUpdates): array
    {
        /** @var ViewablePackageUpdate[] $officialPackages */
        $officialPackages = [];
        /** @var ViewablePackageUpdate[] $thirdPartySources */
        $thirdPartySources = [];
        /** @var ViewablePackageUpdate[] $trustedSources */
        $trustedSources = [];

        foreach ($packageUpdates as $packageUpdate) {
            if ($packageUpdate->getUpdateServer()->isWoltLabUpdateServer()) {
                $officialPackages[] = $packageUpdate;
            } elseif ($packageUpdate->getUpdateServer()->isTrustedServer() || $packageUpdate->getUpdateServer()->isWoltLabStoreServer()) {
                $trustedSources[] = $packageUpdate;
            } else {
                $thirdPartySources[] = $packageUpdate;
            }
        }

        \uasort($officialPackages, static function (ViewablePackageUpdate $a, ViewablePackageUpdate $b) {
            return \strnatcasecmp($a->getName(), $b->getName());
        });
        \uasort($thirdPartySources, static function (ViewablePackageUpdate $a, ViewablePackageUpdate $b) {
            return \strnatcasecmp($a->getName(), $b->getName());
        });
        \uasort($trustedSources, static function (ViewablePackageUpdate $a, ViewablePackageUpdate $b) {
            return \strnatcasecmp($a->getName(), $b->getName());
        });

        return [
            'count' => \count($officialPackages) + \count($thirdPartySources) + \count($trustedSources),
            'template' => WCF::getTPL()->render('wcf', 'packageSearchResultList', [
                'officialPackages' => $officialPackages,
                'thirdPartySources' => $thirdPartySources,
                'trustedSources' => $trustedSources,
            ]),
        ];
    }

    /**
     * Validates parameters to perform a system update.
     *
     * @return void
     */
    public function validatePrepareUpdate()
    {
        WCF::getSession()->checkPermissions(['admin.configuration.package.canUpdatePackage']);

        if (!isset($this->parameters['packages']) || !\is_array($this->parameters['packages'])) {
            throw new UserInputException('packages');
        }

        // validate packages for their existence
        $availableUpdates = PackageUpdateDispatcher::getInstance()->getAvailableUpdates();
        foreach ($this->parameters['packages'] as $packageName => $versionNumber) {
            $isValid = false;

            foreach ($availableUpdates as $package) {
                if ($package['package'] === $packageName) {
                    // validate version
                    if (isset($package['versions'][$versionNumber])) {
                        $isValid = true;
                        break;
                    }
                }
            }

            if (!$isValid) {
                throw new UserInputException('packages');
            }
        }

        if (isset($this->parameters['authData'])) {
            if (!\is_array($this->parameters['authData'])) {
                throw new UserInputException('authData');
            }

            $this->readInteger('packageUpdateServerID', false, 'authData');
            $this->readString('password', false, 'authData');
            $this->readString('username', false, 'authData');
            $this->readBoolean('saveCredentials', true, 'authData');
            $this->parameters['authData']['isStoreCode'] = false;
        }
    }

    /**
     * Prepares a system update.
     *
     * @return QueueEntry
     */
    public function prepareUpdate()
    {
        return $this->createQueue('update');
    }

    /**
     * Validates parameters to prepare a package installation.
     *
     * @return void
     */
    public function validatePrepareInstallation()
    {
        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        if (!isset($this->parameters['packages']) || !\is_array($this->parameters['packages']) || \count($this->parameters['packages']) !== 1) {
            throw new UserInputException('packages');
        }

        if (isset($this->parameters['authData'])) {
            if (!\is_array($this->parameters['authData'])) {
                throw new UserInputException('authData');
            }

            $this->readInteger('packageUpdateServerID', true, 'authData');
            if ($this->parameters['authData']['packageUpdateServerID'] === 0) {
                $this->parameters['authData']['packageUpdateServerID'] = PackageUpdateServer::getPluginStoreServer()->packageUpdateServerID;
            }

            $this->readString('password', false, 'authData');
            $this->readString('username', false, 'authData');
            $this->readBoolean('saveCredentials', true, 'authData');
            $this->readBoolean('isStoreCode', true, 'authData');
        }
    }

    /**
     * Prepares a package installation.
     *
     * @return QueueEntry
     */
    public function prepareInstallation()
    {
        return $this->createQueue('install');
    }

    /**
     * @return void
     */
    public function validateRefreshDatabase()
    {
        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        $this->readBoolean('ignoreCache', true);

        if (\ENABLE_BENCHMARK !== 0) {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->getDynamicVariable('wcf.acp.package.searchForUpdates.benchmark')
            ));
        }
    }

    /**
     * @return void
     */
    public function refreshDatabase()
    {
        PackageUpdateDispatcher::getInstance()->refreshPackageDatabase();
    }

    /**
     * @return QueueEntry
     * @throws SystemException
     */
    protected function createQueue(string $queueType)
    {
        if (isset($this->parameters['authData'])) {
            PackageUpdateServer::storeAuthData(
                $this->parameters['authData']['packageUpdateServerID'],
                $this->parameters['authData']['username'],
                $this->parameters['authData']['password'],
                $this->parameters['authData']['saveCredentials']
            );

            if ($this->parameters['authData']['isStoreCode'] === true) {
                PackageUpdateServer::enableSecureMode();
            }
        }

        $scheduler = new PackageInstallationScheduler($this->parameters['packages']);

        try {
            $scheduler->buildPackageInstallationStack($queueType === 'install');
        } catch (PackageUpdateUnauthorizedException $e) {
            return [
                'template' => $e->getRenderedTemplate(),
                'type' => 'authorizationRequired',
            ];
        }

        // validate exclusions
        $excludedPackages = $scheduler->getExcludedPackages();

        if ($excludedPackages !== []) {
            return [
                'template' => WCF::getTPL()->render(
                    'wcf',
                    'packageUpdateExcludedPackages',
                    ['excludedPackages' => $excludedPackages]
                ),
                'type' => 'conflict',
            ];
        }

        $stack = $scheduler->getPackageInstallationStack();

        // detect major upgrades of the Core itself
        if ($queueType === 'update') {
            for ($i = 0, $length = \count($stack); $i < $length; $i++) {
                $update = $stack[$i];
                if ($update['package'] === 'com.woltlab.wcf') {
                    \preg_match('~^(?P<version>\d+\.\d+)\.~', $update['fromversion'], $matchFromVersion);
                    \preg_match('~^(?P<version>\d+\.\d+)\.~', $update['toVersion'], $matchToVersion);
                    if ($matchFromVersion['version'] !== $matchToVersion['version']) {
                        // version mismatch, this is a major upgrade
                        if ($i > 0) {
                            // the Core upgrade must be the first package in the stack, but there appears to be
                            // at least one package in front of the queue, therefore there are outstanding
                            // updates for the previous version line
                            throw new SystemException(WCF::getLanguage()->getDynamicVariable('wcf.acp.package.update.error.outstandingUpdates'));
                        }
                    }

                    break;
                }
            }
        }

        $queueID = null;
        if ($stack !== []) {
            $parentQueueID = 0;
            $processNo = PackageInstallationQueue::getNewProcessNo();
            foreach ($stack as $package) {
                $queue = PackageInstallationQueueEditor::create([
                    'parentQueueID' => $parentQueueID,
                    'processNo' => $processNo,
                    'userID' => WCF::getUser()->userID,
                    'package' => $package['package'],
                    'packageName' => $package['packageName'],
                    'packageID' => $package['packageID'] ?: null,
                    'archive' => $package['archive'],
                    'action' => $package['action'],
                ]);
                $parentQueueID = $queue->queueID;

                if ($queueID === null) {
                    $queueID = $queue->queueID;
                }
            }
        }

        return [
            'queueID' => $queueID,
            'type' => 'queue',
        ];
    }
}
