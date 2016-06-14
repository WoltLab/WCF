<?php
namespace wcf\system\package\plugin;
use wcf\system\package\PackageArchive;

/**
 * Every PackageInstallationPlugin has to implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
interface IPackageInstallationPlugin {
	/**
	 * Executes the installation of this plugin.
	 */
	public function install();
	
	/**
	 * Executes the update of this plugin.
	 */
	public function update();
	
	/**
	 * Returns true if the uninstallation of the given package should execute
	 * this plugin.
	 * 
	 * @return	boolean
	 */
	public function hasUninstall();
	
	/**
	 * Executes the uninstallation of this plugin.
	 */
	public function uninstall();
	
	/**
	 * Returns the default file name containing the instructions. If no default
	 * file name is supported, null is returned.
	 * 
	 * @return	string
	 * @since	3.0
	 */
	public static function getDefaultFilename();
	
	/**
	 * Validates if the passed instruction is valid for this package installation plugin. If anything is
	 * wrong with it, this method should return false.
	 * 
	 * @param	\wcf\system\package\PackageArchive	$packageArchive
	 * @param	string					$instruction
	 * @return	boolean
	 */
	public static function isValid(PackageArchive $packageArchive, $instruction);
}
