<?php

namespace wcf\acp\page;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\page\AbstractGridViewPage;
use wcf\system\application\ApplicationHandler;
use wcf\system\gridView\admin\PackageGridView;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows a list of all installed packages.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<PackageGridView>
 */
final class PackageListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.package.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = [
        'admin.configuration.package.canUpdatePackage',
        'admin.configuration.package.canInstallPackage',
    ];

    #[\Override]
    protected function createGridView(): PackageGridView
    {
        return new PackageGridView();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        $taintedApplications = [];
        foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
            if (!$application->isTainted) {
                continue;
            }

            $taintedApplications[$application->getPackage()->packageID] = $application;
        }

        WCF::getTPL()->assign([
            'recentlyDisabledCustomValues' => LanguageFactory::getInstance()->countRecentlyDisabledCustomValues(),
            'taintedApplications' => $taintedApplications,
            'availableUpgradeVersion' => WCF::AVAILABLE_UPGRADE_VERSION,
            'upgradeOverrideEnabled' => PackageUpdateServer::isUpgradeOverrideEnabled(),
        ]);
    }
}
