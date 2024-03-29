<?php

namespace wcf\system\package\plugin;

use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\WCF;

/**
 * Executes individual database scripts during installation.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DatabasePackageInstallationPlugin extends AbstractPackageInstallationPlugin implements IIdempotentPackageInstallationPlugin
{
    public const SCRIPT_DIR = 'acp/database/';

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

        $this->updateDatabase($packageDir . $this->instruction['value']);
    }

    /**
     * Runs the database script at the given path.
     */
    private function updateDatabase(string $scriptPath): void
    {
        $tables = include($scriptPath);
        if (!\is_array($tables)) {
            throw new \UnexpectedValueException("A database script must return an array.");
        }

        (new DatabaseTableChangeProcessor(
            $this->installation->getPackage(),
            $tables,
            WCF::getDB()->getEditor()
        ))->process();
    }

    /**
     * @inheritDoc
     */
    public function hasUninstall()
    {
        // Database scripts cannot be uninstalled.
        return false;
    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public static function getDefaultFilename()
    {
        return static::SCRIPT_DIR . '*.php';
    }

    /**
     * @inheritDoc
     */
    public static function getSyncDependencies()
    {
        return ['file'];
    }
}
