<?php
namespace wcf\system\package\plugin;
use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\package\FilesFileHandler;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\util\StyleUtil;

/**
 * Installs, updates and deletes files.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class FilePackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $tableName = 'package_installation_file_log';
	
	/**
	 * @inheritDoc
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
			$statement->execute([
				$this->installation->getPackageID(),
				'config.inc.php',
				Package::getAbbreviation($this->installation->getPackage()->package)
			]);
			
			// load application
			WCF::loadRuntimeApplication($this->installation->getPackageID());
		}
		
		// delete temporary sourceArchive
		@unlink($sourceFile);
		
		// update acp style file
		StyleUtil::updateStyleFile();
	}
	
	/**
	 * @inheritDoc
	 */
	public function uninstall() {
		// fetch files from log
		$sql = "SELECT	filename, application
			FROM	wcf".WCF_N."_package_installation_file_log
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
		
		$files = [];
		while ($row = $statement->fetchArray()) {
			if (!isset($files[$row['application']])) {
				$files[$row['application']] = [];
			}
			
			$files[$row['application']][] = $row['filename'];
		}
		
		foreach ($files as $application => $filenames) {
			/** @noinspection PhpUndefinedMethodInspection */
			$this->installation->deleteFiles(Application::getDirectory($application), $filenames);
			
			// delete log entries
			parent::uninstall();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDefaultFilename() {
		return 'files.tar';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isValid(PackageArchive $archive, $instruction) {
		if (!$instruction) {
			$instruction = static::getDefaultFilename();
		}
		
		if (preg_match('~\.(tar(\.gz)?|tgz)$~', $instruction)) {
			// check if file actually exists
			try {
				if ($archive->getTar()->getIndexByFilename($instruction) === false) {
					return false;
				}
			}
			catch (\SystemException $e) {
				return false;
			}
			
			return true;
		}
		
		return false;
	}
}
