<?php
namespace wcf\system\package;
use wcf\system\setup\IFileHandler;

/**
 * PackageInstallationFileHandler is the abstract FileHandler implementation for all file installations during the package installation.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
abstract class PackageInstallationFileHandler implements IFileHandler {
	/**
	 * active package installation dispatcher
	 * @var	wcf\system\package\PackageInstallationDispatcher
	 */
	protected $packageInstallation;
	
	/**
	 * Creates a new PackageInstallationFileHandler object.
	 * 
	 * @param	wcf\system\package\PackageInstallationDispatcher	$packageInstallation
	 */
	public function __construct(PackageInstallationDispatcher $packageInstallation) {
		$this->packageInstallation = $packageInstallation;
	}
}
