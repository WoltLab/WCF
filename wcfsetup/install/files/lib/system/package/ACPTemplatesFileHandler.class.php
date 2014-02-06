<?php
namespace wcf\system\package;
use wcf\data\package\Package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * File handler implementation for the installation of ACP template files.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
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
	 * @see	\wcf\system\setup\IFileHandler::checkFiles()
	 */
	public function checkFiles(array $files) {
		if ($this->packageInstallation->getPackage()->package != 'com.woltlab.wcf') {
			// check if files are existing already
			if (!empty($files)) {
				foreach ($files as &$file) {
					$file = substr($file, 0, -4);
				}
				unset($file);
				
				// get by other packages registered files
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add('packageID <> ?', array($this->packageInstallation->getPackageID()));
				$conditions->add('templateName IN (?)', array($files));
				$conditions->add('application = ?', array($this->application));
				
				$sql = "SELECT	packageID, templateName
					FROM	wcf".WCF_N."_".$this->tableName."
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				
				$lockedFiles = array();
				while ($row = $statement->fetchArray()) {
					$lockedFiles[$row['templateName']] = $row['packageID'];
				}
				
				// check if acp templates from the package beeing
				// installed are in conflict with already installed
				// files
				if (!$this->packageInstallation->getPackage()->isApplication && !empty($lockedFiles)) {
					foreach ($files as $file) {
						if (isset($lockedFiles[$file])) {
							$owningPackage = new Package($lockedFiles[$file]);
							
							throw new SystemException("A package can't overwrite templates from other packages. Only an update from the package which owns the template can do that. (Package '".$this->packageInstallation->getPackage()->package."' tries to overwrite template '".$file."', which is owned by package '".$owningPackage->package."')");
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
		// remove file extension
		foreach ($files as &$file) {
			$file = substr($file, 0, -4);
		}
		unset($file);
		
		// fetch already installed acp templates
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add('packageID = ?', array($this->packageInstallation->getPackageID()));
		$conditions->add('templateName IN (?)', array($files));
		$conditions->add('application = ?', array($this->application));
		
		$sql = "SELECT	templateName
			FROM	wcf".WCF_N."_".$this->tableName."
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		while ($templateName = $statement->fetchColumn()) {
			$index = array_search($templateName, $files);
			
			if ($index !== false) {
				unset($files[$index]);
			}
		}
		
		if (!empty($files)) {
			$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."
						(packageID, templateName, application)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($files as $file) {
				$statement->execute(array(
					$this->packageInstallation->getPackageID(),
					$file,
					$this->application
				));
			}
		}
	}
}
