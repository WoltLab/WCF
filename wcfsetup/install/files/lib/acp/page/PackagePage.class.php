<?php

namespace wcf\acp\page;

use wcf\data\package\Package;
use wcf\http\Helper;
use wcf\page\AbstractPage;
use wcf\system\gridView\admin\DependentPackageGridView;
use wcf\system\gridView\admin\RequiredPackageGridView;
use wcf\system\interaction\admin\PackageInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows all information about an installed package.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackagePage extends AbstractPage
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

    /**
     * package object
     * @var ?Package
     */
    public $package;

    /**
     * Plugin-Store fileID
     * @var int
     */
    public $pluginStoreFileID = 0;

    protected RequiredPackageGridView $requiredPackageGridView;
    protected DependentPackageGridView $dependentPackageGridView;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->package = Helper::fetchObjectFromQueryParameter(Package::class);
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        $sql = "SELECT  pluginStoreFileID
                FROM    wcf1_package_update
                WHERE   package = ?
                    AND pluginStoreFileID <> 0";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->package->package]);
        $this->pluginStoreFileID = \intval($statement->fetchSingleColumn());

        $this->requiredPackageGridView = new RequiredPackageGridView($this->package->packageID);
        $this->dependentPackageGridView = new DependentPackageGridView($this->package->packageID);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'package' => $this->package,
            'pluginStoreFileID' => $this->pluginStoreFileID,
            'requiredPackageGridView' => $this->requiredPackageGridView,
            'dependentPackageGridView' => $this->dependentPackageGridView,
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new PackageInteractions(),
                $this->package,
                LinkHandler::getInstance()->getControllerLink(PackageListPage::class)
            ),
        ]);
    }
}
