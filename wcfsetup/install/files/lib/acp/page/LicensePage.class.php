<?php

namespace wcf\acp\page;

use GuzzleHttp\Exception\ConnectException;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\acp\form\LicenseEditForm;
use wcf\data\package\Package;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\ViewablePackageUpdate;
use wcf\page\AbstractPage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\NamedUserException;
use wcf\system\package\InstallablePackageResolver;
use wcf\system\package\license\exception\ParsingFailed;
use wcf\system\package\license\LicenseApi;
use wcf\system\package\license\LicenseData;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HtmlString;

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

    /**
     * @var array<string, array<string, string>>
     */
    private array $availablePackages = [];

    /**
     * @var array<string, Package>
     */
    private array $installedPackages;

    /**
     * @var array<string, string>
     */
    private array $installablePackages = [];

    /**
     * @var array<string, ViewablePackageUpdate>
     */
    private array $packageUpdates = [];

    /**
     * @var array<string, string>
     */
    private array $requiresLicenseExtension = [];

    private const CURRENT_MAJOR = '6.0';

    #[\Override]
    public function readData()
    {
        parent::readData();

        if (PackageUpdateServer::isUpgradeOverrideEnabled()) {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->get('wcf.acp.license.error.upgradeOverrideEnabled')
            ));
        }

        if (!LicenseApi::hasLicenseCredentials()) {
            $this->setPsr7Response(new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(
                    LicenseEditForm::class,
                    [
                        'url' => LinkHandler::getInstance()->getControllerLink(LicensePage::class),
                    ],
                ),
                303
            ));

            return;
        }

        PackageUpdateDispatcher::getInstance()->refreshPackageDatabase();

        $licenseApi = new LicenseApi();
        try {
            $licenseData = $licenseApi->getUpToDateLicenseData();
        } catch (ConnectException | ClientExceptionInterface) {
            $this->setPsr7Response(new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(
                    LicenseEditForm::class,
                    [
                        'failedValidation' => 1,
                        'url' => LinkHandler::getInstance()->getControllerLink(LicensePage::class),
                    ],
                ),
                303
            ));

            return;
        } catch (ParsingFailed $e) {
            if (\ENABLE_DEBUG_MODE !== 0 && \ENABLE_DEVELOPER_TOOLS !== 0) {
                throw $e;
            }

            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.license.error.parsingFailed',
                    [
                        'licenseData' => $licenseApi->readFromFile(),
                    ]
                )
            ));
        }

        $this->licenseData = $licenseData;

        $identifiers = \array_merge(
            \array_keys($this->licenseData->woltlab),
            \array_keys($this->licenseData->pluginstore)
        );
        $this->installedPackages = $this->getInstalledPackages($identifiers);

        $identifiers = \array_diff($identifiers, \array_keys($this->installedPackages));

        $packageUpdates = new InstallablePackageResolver()->resolveByIdentifiersRegardlessOfLicense(
            \array_values($identifiers)
        );
        foreach ($packageUpdates as $packageUpdate) {
            $updateServer = $packageUpdate->getUpdateServer();
            if ($updateServer->isWoltLabUpdateServer() || $updateServer->isWoltLabStoreServer()) {
                $identifier = $packageUpdate->package;
                $this->installablePackages[$identifier] = $packageUpdate->getAccessibleVersion()->packageVersion;
                $this->packageUpdates[$identifier] = $packageUpdate;
            }
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

    #[\Override]
    public function assignVariables(): void
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
     * @param string[] $identifiers
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
}
