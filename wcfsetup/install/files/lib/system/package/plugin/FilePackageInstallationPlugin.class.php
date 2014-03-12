<?php
namespace wcf\system\package\plugin;
use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\package\FilesFileHandler;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\util\StyleUtil;

/**
 * Installs, updates and deletes files.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class FilePackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'package_installation_file_log';
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		$abbreviation = 'wcf';
		if (isset($this->instruction['attributes']['application'])) {
			$abbreviation = $this->instruction['attributes']['application'];
		}
		else if ($this->installation->getPackage()->isApplication) {
			$abbreviation = Package::getAbbreviation($this->installation->getPackage()->package);
		}
		
		// absolute path to package dir
		$packageDir = Application::getDirectory($abbreviation);
		
		// extract files.tar to temp folder
		$sourceFile = $this->installation->getArchive()->extractTar($this->instruction['value'], 'files_');
		
		// create file handler
		$fileHandler = new FilesFileHandler($this->installation, $abbreviation);
		
		// extract content of files.tar
		$fileInstaller = $this->installation->extractFiles($packageDir, $sourceFile, $fileHandler);
		
		// if this a an application, write config.inc.php for this package
		if ($this->installation->getPackage()->isApplication == 1 && $this->installation->getPackage()->package != 'com.woltlab.wcf' && $this->installation->getAction() == 'install' && $abbreviation != 'wcf') {
			// touch file
			$fileInstaller->touchFile(PackageInstallationDispatcher::CONFIG_FILE);
			
			// create file
			Package::writeConfigFile($this->installation->getPackageID());
			
			// log file
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_file_log
						(packageID, filename, application)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->installation->getPackageID(),
				'config.inc.php',
				Package::getAbbreviation($this->installation->getPackage()->package)
			));
			
			// load application
			WCF::loadRuntimeApplication($this->installation->getPackageID());
		}
		
		// delete temporary sourceArchive
		@unlink($sourceFile);
		
		// update acp style file
		StyleUtil::updateStyleFile();
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// fetch files from log
		$sql = "SELECT	filename, application
			FROM	wcf".WCF_N."_package_installation_file_log
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		
		$files = array();
		while ($row = $statement->fetchArray()) {
			if (!isset($files[$row['application']])) {
				$files[$row['application']] = array();
			}
			
			$files[$row['application']][] = $row['filename'];
		}
		
		foreach ($files as $application => $filenames) {
			$this->installation->deleteFiles(Application::getDirectory($application), $filenames);
			
			// delete log entries
			parent::uninstall();
		}
	}
}
