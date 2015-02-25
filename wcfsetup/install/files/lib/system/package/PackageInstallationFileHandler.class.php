<?php
namespace wcf\system\package;
use wcf\system\setup\IFileHandler;

/**
 * Abstract file handler implementation for all file installations during the package
 * installation.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
abstract class PackageInstallationFileHandler implements IFileHandler {
	/**
	 * abbrevation of the application the files belong to
	 * @var	array<string>
	 */
	protected $application = '';
	
	/**
	 * active package installation dispatcher
	 * @var	\wcf\system\package\PackageInstallationDispatcher
	 */
	protected $packageInstallation;
	
	/**
	 * Creates a new PackageInstallationFileHandler object.
	 * 
	 * @param	\wcf\system\package\PackageInstallationDispatcher	$packageInstallation
	 */
	public function __construct(PackageInstallationDispatcher $packageInstallation, $application) {
		$this->packageInstallation = $packageInstallation;
		$this->application = $application;
	}
}
