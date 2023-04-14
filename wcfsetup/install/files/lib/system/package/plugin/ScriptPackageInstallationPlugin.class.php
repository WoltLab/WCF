<?php

namespace wcf\system\package\plugin;

use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\cache\CacheHandler;

/**
 * Executes individual PHP scripts during installation.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ScriptPackageInstallationPlugin extends AbstractPackageInstallationPlugin
{
    /**
     * @inheritDoc
     */
    public function install()
    {
        parent::install();

        $abbreviation = 'wcf';
        if (isset($this->instruction['attributes']['application'])) {
            $abbreviation = $this->instruction['attributes']['application'];
        } elseif ($this->installation->getPackage()->isApplication) {
            $abbreviation = Package::getAbbreviation($this->installation->getPackage()->package);
        }

        $packageDir = Application::getDirectory($abbreviation);

        $flushCache = true;
        if (
            isset($this->instruction['attributes']['flushCache'])
            && $this->instruction['attributes']['flushCache'] === 'false'
        ) {
            $flushCache = false;
        }

        // reset WCF cache
        if ($flushCache) {
            CacheHandler::getInstance()->flushAll();
        }

        return $this->run($packageDir . $this->instruction['value']);
    }

    /**
     * Runs the script with the given path.
     */
    private function run(string $scriptPath)
    {
        return include($scriptPath);
    }

    /**
     * @inheritDoc
     */
    public function hasUninstall()
    {
        // scripts can't be uninstalled
        return false;
    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {
        // does nothing
    }
}
