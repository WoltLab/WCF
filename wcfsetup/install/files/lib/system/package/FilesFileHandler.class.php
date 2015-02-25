<?php
namespace wcf\system\package;
use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * File handler implementation for the installation of regular files.
 * 
 * @author	Matthias Schmidt, Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class FilesFileHandler extends PackageInstallationFileHandler {
	/**
	 * @see	\wcf\system\setup\IFileHandler::checkFiles()
	 */
	public function checkFiles(array $files) {
		if ($this->packageInstallation->getPackage()->package != 'com.woltlab.wcf') {
			if (!empty($files)) {
				// get registered files of other packages for the
				// same application
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add('packageID <> ?', array($this->packageInstallation->getPackageID()));
				$conditions->add('filename IN (?)', array($files));
				$conditions->add('application = ?', array($this->application));
				
				$sql = "SELECT	filename, packageID
					FROM	wcf".WCF_N."_package_installation_file_log
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				$lockedFiles = array();
				while ($row = $statement->fetchArray()) {
					$lockedFiles[$row['filename']] = $row['packageID'];
				}
				
				// check delivered files
				if (!empty($lockedFiles)) {
					foreach ($files as $key => $file) {
						if (isset($lockedFiles[$file])) {
							$owningPackage = new Package($lockedFiles[$file]);
							
							throw new SystemException("A package can't overwrite files from other packages. Only an update from the package which owns the file can do that. (Package '".$this->packageInstallation->getPackage()->package."' tries to overwrite file '".$file."', which is owned by package '".$owningPackage->package."')");
						}
					}
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\system\setup\IFileHandler::logFiles()
	 */
	public function logFiles(array $files) {
		if (empty($files)) {
			return;
		}
		
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_installation_file_log
							(packageID, filename, application)
				VALUES			(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($files as $file) {
			$statement->execute(array(
				$this->packageInstallation->getPackageID(),
				$file,
				$this->application
			));
		}
		WCF::getDB()->commitTransaction();
	}
}
