<?php

namespace wcf\acp\page;

use wcf\data\package\I18nPackageList;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\page\SortablePage;
use wcf\system\application\ApplicationHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows a list of all installed packages.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    I18nPackageList $objectList
 */
class PackageListPage extends SortablePage
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
        'admin.configuration.package.canUninstallPackage',
    ];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 50;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'packageType';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'packageID',
        'package',
        'packageDir',
        'packageNameI18n',
        'packageDescription',
        'packageDate',
        'packageURL',
        'isApplication',
        'author',
        'authorURL',
        'installDate',
        'updateDate',
    ];

    /**
     * @inheritDoc
     */
    public $objectListClassName = I18nPackageList::class;

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        $this->sqlOrderBy = ($this->sortField == 'packageNameI18n' ? '' : 'package.') . ($this->sortField == 'packageType' ? 'isApplication ' . $this->sortOrder : $this->sortField . ' ' . $this->sortOrder) . ($this->sortField != 'packageNameI18n' ? ', packageNameI18n ASC' : '');

        parent::readObjects();
    }
}
