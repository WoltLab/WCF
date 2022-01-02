<?php

namespace wcf\acp\page;

use wcf\page\AbstractPage;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\WCF;

/**
 * Shows the package update confirmation form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Page
 */
class PackageUpdatePage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.package';

    /**
     * list of available updates
     * @var array
     */
    public $availableUpdates = [];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.package.canUpdatePackage'];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->availableUpdates = PackageUpdateDispatcher::getInstance()->getAvailableUpdates(true, true);

        // Reduce the versions into a single value.
        foreach ($this->availableUpdates as &$update) {
            $latestVersion = reset($update['versions']);
            $update['newVersion'] = $latestVersion['packageVersion'];
        }
        unset($update);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'availableUpdates' => $this->availableUpdates,
        ]);
    }
}
