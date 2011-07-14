<?php
namespace wcf\system\package\plugin;

/**
 * Any PackageInstallationPlugin should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
interface PackageInstallationPlugin {
	/**
	 * Executes the installation of this plugin.
	 */
	public function install();
	
	/**
	 * Executes the update of this plugin.
	 */
	public function update();
	
	/**
	 * Returns true, if the uninstallation of the given package should execute this plugin.
	 * 
	 * @return	boolean
	 */
	public function hasUninstall();
	
	/**
	 * Executes the uninstallation of this plugin.
	 */
	public function uninstall();
}
