<?php
namespace wcf\system\package;
use wcf\system\setup\IFileHandler;

/**
 * Abstract file handler implementation for all file installations during the package
 * installation.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package
 */
abstract class PackageInstallationFileHandler implements IFileHandler {
	/**
	 * abbrevation of the application the files belong to
	 * @var	string
	 */
	protected $application = '';
	
	/**
	 * active package installation dispatcher
	 * @var	PackageInstallationDispatcher
	 */
	protected $packageInstallation;
	
	/**
	 * Creates a new PackageInstallationFileHandler object.
	 * 
	 * @param	PackageInstallationDispatcher	$packageInstallation
	 * @param	string		$application
	 */
	public function __construct(PackageInstallationDispatcher $packageInstallation, $application) {
		$this->packageInstallation = $packageInstallation;
		$this->application = $application;
	}
}
