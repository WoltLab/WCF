<?php

namespace wcf\system\package\plugin;

use wcf\system\package\PackageArchive;

/**
 * Every PackageInstallationPlugin has to implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IPackageInstallationPlugin
{
    /**
     * Executes the installation of this plugin.
     *
     * @return mixed
     */
    public function install();

    /**
     * Executes the update of this plugin.
     *
     * @return mixed
     */
    public function update();

    /**
     * Returns true if the uninstallation of the given package should execute
     * this plugin.
     *
     * @return  bool
     */
    public function hasUninstall();

    /**
     * Executes the uninstallation of this plugin.
     *
     * @return void
     */
    public function uninstall();

    /**
     * Returns the default file name containing the instructions or `null` if no default
     * file name is supported.
     *
     * @return  ?string
     */
    public static function getDefaultFilename();

    /**
     * Validates if the passed instruction is valid for this package installation plugin. If anything is
     * wrong with it, this method should return false.
     *
     * @return  bool
     */
    public static function isValid(PackageArchive $packageArchive, string $instruction);
}
