<?php
namespace wcf\system\package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * FilesFileHandler is a FileHandler implementation for the installation of regular files.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class FilesFileHandler extends PackageInstallationFileHandler {
	/**
	 * @see	wcf\system\setup\IFileHandler::checkFiles()
	 */
	public function checkFiles(array $files) {
		if ($this->packageInstallation->getPackage()->package != 'com.woltlab.wcf') {
			if (!empty($files)) {
				// get by other packages registered files
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("file_log.packageID <> ?", array($this->packageInstallation->getPackageID()));
				$conditions->add("file_log.filename IN (?)", array($files));
				
				$sql = "SELECT		file_log.filename, package.packageDir
					FROM		wcf".WCF_N."_package_installation_file_log file_log
					LEFT JOIN	wcf".WCF_N."_package package
					ON		(package.packageID = file_log.packageID)
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				$lockedFiles = array();
				while ($row = $statement->fetchArray()) {
					$lockedFiles[$row['packageDir'].$row['filename']] = true;
				}
				
				// check delivered files
				if (!empty($lockedFiles)) {
					$dir = $this->packageInstallation->getPackage()->packageDir;
					foreach ($files as $key => $file) {
						if (isset($lockedFiles[$dir.$file])) {
							unset($files[$key]);
						}
					}
				}
			}
		}
	}
	
	/**
	 * @see	wcf\system\setup\IFileHandler::logFiles()
	 */
	public function logFiles(array $files) {
		if (empty($files)) {
			return;
		}
		
		// fetch already installed files
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("packageID = ?", array($this->packageInstallation->getPackageID()));
		$conditions->add("filename IN (?)", array($files));
		
		$sql = "SELECT	filename
			FROM	wcf".WCF_N."_package_installation_file_log
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$installedFiles = array();
		while ($row = $statement->fetchArray()) {
			$installedFiles[] = $row['filename'];
		}
		
		// ignore files which have already been installed
		$installFiles = array();
		foreach ($files as $file) {
			if (in_array($file, $installedFiles)) {
				continue;
			}
			
			$installFiles[] = $file;
		}
		
		if (!empty($installFiles)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_file_log
						(packageID, filename)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($installFiles as $file) {
				$statement->execute(array(
					$this->packageInstallation->getPackageID(),
					$file
				));
			}
		}
	}
}
