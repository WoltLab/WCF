<?php
namespace wcf\system\package\plugin;
use wcf\data\package\Package;
use wcf\data\package\PackageEditor;
use wcf\system\WCF;
use wcf\system\exception\SystemException;
use wcf\system\package\FilesFileHandler;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\util\FileUtil;
use wcf\util\StyleUtil;

/**
 * This PIP installs, updates or deletes files delivered by a package.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class FilePackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */	
	public $tableName = 'package_installation_file_log';
	
	/**
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		// get package installation dir
		$dir = $this->installation->getPackage()->packageDir;
		if (empty($dir)) {
			if ($this->installation->getPackage()->parentPackageID > 0) {
				// plugin
				// use parents package dir
				$dir = $this->installation->getPackage()->getParentPackage()->packageDir;
			}
			else if ($this->installation->getPackage()->isApplication == 1 && $this->installation->getPackage()->package != 'com.woltlab.wcf' && $this->installation->getAction() == 'install') {
				// application
				// prompt package dir
				$dir = $this->promptPackageDir();
			}
			
			// save package dir
			if (!empty($dir)) {
				$package = new Package($this->installation->getPackageID());
				$packageEditor = new PackageEditor($package);
				$packageEditor->update(array('packageDir' => $dir));
				
				$this->installation->getPackage()->packageDir = $dir;
			}
		}
		
		// absolute path to package dir
		$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$dir));
		
		// extract files.tar to temp folder
		$sourceFile = $this->installation->getArchive()->extractTar($this->instruction['value'], 'files_');
		
		// create file handler
		$fileHandler = new FilesFileHandler($this->installation);
		
		// extract content of files.tar
		$fileInstaller = $this->installation->extractFiles($packageDir, $sourceFile, $fileHandler);
		
		// if this a an application, write config.inc.php for this package
		if ($this->installation->getPackage()->isApplication == 1 && $this->installation->getPackage()->package != 'com.woltlab.wcf' && $this->installation->getAction() == 'install') {
			// touch file
			$fileInstaller->touchFile(PackageInstallationDispatcher::CONFIG_FILE);
			
			// create file
			Package::writeConfigFile($this->installation->getPackageID());
			
			// log file
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_file_log
						(packageID, filename)
				VALUES		(?, 'config.inc.php')";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->installation->getPackageID()));
		}
		
		// delete temporary sourceArchive
		@unlink($sourceFile);
		
		// update acp style file
		StyleUtil::updateStyleFile();
	}
	
	/**
	 * Uninstalls the files of this package.
	 */
	public function uninstall() {
		// get absolute package dir
		$packageDir = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator(realpath(WCF_DIR.$this->installation->getPackage()->packageDir)));
		
		// create file list
		$files = array();
		
		// get files from log
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_installation_file_log
			WHERE 	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		while ($row = $statement->fetchArray()) {
			$files[] = $row['filename'];
		}
		
		if (count($files) > 0) {
			// delete files
			$this->installation->deleteFiles($packageDir, $files);
			
			// delete log entries
			parent::uninstall();
		}
	}
}
