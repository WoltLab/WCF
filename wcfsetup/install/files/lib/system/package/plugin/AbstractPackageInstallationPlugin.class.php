<?php

namespace wcf\system\package\plugin;

use wcf\system\event\EventHandler;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;

/**
 * Abstract implementation of a package installation plugin.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractPackageInstallationPlugin implements IPackageInstallationPlugin
{
    /**
     * table application prefix
     * @var string
     */
    public $application = 'wcf';

    /**
     * database table name
     * @var string
     */
    public $tableName = '';

    /**
     * active instance of PackageInstallationDispatcher
     * @var PackageInstallationDispatcher
     */
    public $installation;

    /**
     * install/update instructions
     * @var array
     */
    public $instruction = [];

    /**
     * Creates a new AbstractPackageInstallationPlugin object.
     *
     * @param PackageInstallationDispatcher $installation
     * @param array $instruction
     */
    public function __construct(PackageInstallationDispatcher $installation, $instruction = [])
    {
        $this->installation = $installation;
        $this->instruction = $instruction;

        // call 'construct' event
        EventHandler::getInstance()->fireAction($this, 'construct');
    }

    /**
     * @inheritDoc
     */
    public function install()
    {
        // call 'install' event
        EventHandler::getInstance()->fireAction($this, 'install');
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        // call 'update' event
        EventHandler::getInstance()->fireAction($this, 'update');

        return $this->install();
    }

    /**
     * @inheritDoc
     */
    public function hasUninstall()
    {
        // call 'hasUninstall' event
        EventHandler::getInstance()->fireAction($this, 'hasUninstall');

        $sql = "SELECT  COUNT(*)
                FROM    " . $this->application . "1_" . $this->tableName . "
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->installation->getPackageID()]);

        return $statement->fetchSingleColumn() > 0;
    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {
        // call 'uninstall' event
        EventHandler::getInstance()->fireAction($this, 'uninstall');

        $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                WHERE       packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->installation->getPackageID()]);
    }

    /**
     * @see \wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
     * @since   3.0
     */
    public static function getDefaultFilename()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public static function isValid(PackageArchive $packageArchive, $instruction)
    {
        return true;
    }
}
