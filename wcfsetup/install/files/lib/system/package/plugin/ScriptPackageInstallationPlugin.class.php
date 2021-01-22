<?php

namespace wcf\system\package\plugin;

use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\form\FormDocument;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes individual PHP scripts during installation.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Plugin
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
        $path = '';
        if (isset($this->instruction['attributes']['application'])) {
            $abbreviation = $this->instruction['attributes']['application'];
        } elseif ($this->installation->getPackage()->isApplication) {
            $path = FileUtil::getRealPath(WCF_DIR . $this->installation->getPackage()->packageDir);
        }

        if (empty($path)) {
            $dirConstant = \strtoupper($abbreviation) . '_DIR';
            if (!\defined($dirConstant)) {
                throw new SystemException("Cannot execute script-PIP, abbreviation '" . $abbreviation . "' is unknown");
            }

            $path = \constant($dirConstant);
        }

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

        // run script
        $result = $this->run($path . $this->instruction['value']);

        // delete script
        if (!($result instanceof FormDocument) && @\unlink($path . $this->instruction['value'])) {
            // delete file log entry
            $sql = "DELETE FROM wcf" . WCF_N . "_package_installation_file_log
                    WHERE       packageID = ?
                            AND filename = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                $this->installation->getPackageID(),
                $this->instruction['value'],
            ]);
        }

        return $result;
    }

    /**
     * Runs the script with the given path.
     *
     * @param   string      $scriptPath
     */
    private function run($scriptPath)
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
