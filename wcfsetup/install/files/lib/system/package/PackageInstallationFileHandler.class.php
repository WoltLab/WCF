<?php
namespace wcf\system\package;
use wcf\system\setup\FileHandler;

/**
 * PackageInstallationFileHandler is the abstract FileHandler implementation for all file installations during the package installation.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category 	Community Framework
 */
abstract class PackageInstallationFileHandler implements FileHandler {
	protected $packageInstallation;
	
	/**
	 * Creates a new PackageInstallationFileHandler object.
	 * 
	 * @param	PackageInstallationDispatcher	$packageInstallation
	 */
	public function __construct(PackageInstallationDispatcher $packageInstallation) {
		$this->packageInstallation = $packageInstallation;
	}
}
