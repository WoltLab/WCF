<?php
namespace wcf\system\package\plugin;
use wcf\system\package\PackageArchive;

/**
 * Every PackageInstallationPlugin has to implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
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
	 * Validates if the passed instruction is valid for this package installation plugin. If anything is
	 * wrong with it, this method should return false.
	 * 
	 * @param	\wcf\system\package\PackageArchive	$packageArchive
	 * @param	string					$instruction
	 * @return	boolean
	 */
	public static function isValid(PackageArchive $packageArchive, $instruction);
}
