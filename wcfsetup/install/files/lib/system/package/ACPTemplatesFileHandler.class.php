<?php
namespace wcf\system\package;
use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * ACPTemplatesFileHandler is a FileHandler implementation for the installation of ACP-template files.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class ACPTemplatesFileHandler extends PackageInstallationFileHandler {
	/**
	 * name of the database table where the installed files are logged
	 * @var	string
	 */
	protected $tableName = 'acp_template';
	
	/**
	 * @see	wcf\system\setup\IFileHandler::checkFiles()
	 */
	public function checkFiles(array $files) {
		if ($this->packageInstallation->getPackage()->package != 'com.woltlab.wcf') {
			$packageID = $this->packageInstallation->getPackageID();
			
			// build sql string with ACP-templateNames
			$fileNames = array();
			foreach ($files as $file) {
				$fileName = preg_replace("~\.tpl$~", "", $file);
				$fileNames[] = $fileName;
			}
			
			// check if files are existing already
			if (!empty($fileNames)) {
				// get by other packages registered files
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("packageID <> ?", array($packageID));
				$conditions->add("packageID IN (SELECT packageID FROM wcf".WCF_N."_package WHERE packageDir = ? AND isApplication = ?)", array($this->packageInstallation->getPackage()->packageDir, 0));
				$conditions->add("templateName IN (?)", array($fileNames));
				
				$sql = "SELECT		*
					FROM		wcf".WCF_N."_".$this->tableName."
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				
				$lockedFiles = array();
				while ($row = $statement->fetchArray()) {
					$lockedFiles[$row['templateName']] = $row['packageID'];
				}
				
				// check if files from installing package are in conflict with already installed files
				if (!$this->packageInstallation->getPackage()->isApplication && !empty($lockedFiles)) {
					foreach ($fileNames as $key => $file) {
						if (isset($lockedFiles[$file]) && $packageID != $lockedFiles[$file]) {
							$owningPackage = new Package($lockedFiles[$file]);
							throw new SystemException("A non-application package can't overwrite template files. Only an update from the package which owns the template can do that. (Package '".$this->packageInstallation->getPackage()->getPackage()."' tries to overwrite template '".$file."', which is owned by package '".$owningPackage->package."')");
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
		$packageID = $this->packageInstallation->getPackageID();
		
		// remove file extension
		foreach ($files as &$file) {
			$file = preg_replace('~.tpl$~','', $file);
		}
		unset($file);
		
		// get existing templates
		$sql = "SELECT	templateName
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		
		while ($row = $statement->fetchArray()) {
			$index = array_search($row['templateName'], $files);
			
			if ($index !== false) {
				unset($files[$index]);
			}
		}
		
		if (!empty($files)) {
			$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."
						(packageID, templateName)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($files as $file) {
				$statement->execute(array(
					$packageID,
					$file
				));
			}
		}
	}
}
