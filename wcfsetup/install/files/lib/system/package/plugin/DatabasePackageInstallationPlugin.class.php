<?php

namespace wcf\system\package\plugin;

use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes individual database scripts during installation.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Plugin
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
        $path = '';
        if (isset($this->instruction['attributes']['application'])) {
            $abbreviation = $this->instruction['attributes']['application'];
        } elseif ($this->installation->getPackage()->isApplication) {
            $path = FileUtil::getRealPath(WCF_DIR . $this->installation->getPackage()->packageDir);
        }

        if (empty($path)) {
            $dirConstant = \strtoupper($abbreviation) . '_DIR';
            if (!\defined($dirConstant)) {
                throw new \InvalidArgumentException("Cannot execute database PIP, abbreviation '{$abbreviation}' is unknown.");
            }

            $path = \constant($dirConstant);
        }

        $scriptPath = $path . $this->instruction['value'];

        $this->updateDatabase($scriptPath);

        if (@\unlink($scriptPath)) {
            $sql = "DELETE FROM wcf" . WCF_N . "_package_installation_file_log
                    WHERE       packageID = ?
                            AND filename = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                $this->installation->getPackageID(),
                $this->instruction['value'],
            ]);
        }
    }

    /**
     * Runs the database script at the given path.
     *
     * @param   string  $scriptPath
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
