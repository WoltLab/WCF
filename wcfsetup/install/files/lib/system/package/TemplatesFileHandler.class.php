<?php
namespace wcf\system\package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * File handler implementation for the installation of template files.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class TemplatesFileHandler extends ACPTemplatesFileHandler {
	/**
	 * @see	\wcf\system\package\ACPTemplatesFileHandler::$supportsTemplateGroups
	 */
	protected $supportsTemplateGroups = true;
	
	/**
	 * @see	\wcf\system\package\ACPTemplatesFileHandler::$tableName
	 */
	protected $tableName = 'template';
	
	/**
	 * @see	\wcf\system\setup\IFileHandler::logFiles()
	 */
	public function logFiles(array $files) {
		$packageID = $this->packageInstallation->getPackageID();
		
		// remove file extension
		foreach ($files as &$file) {
			$file = substr($file, 0, -4);
		}
		unset($file);
		
		// get existing templates
		$existingTemplates = $updateTemplateIDs = array();
		$sql = "SELECT	templateName, templateID
			FROM	wcf".WCF_N."_template
			WHERE	packageID = ?
				AND application = ?
				AND templateGroupID IS NULL";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID, $this->application));
		while ($row = $statement->fetchArray()) {
			$existingTemplates[$row['templateName']] = $row['templateID'];
		}
		
		// save new templates
		$sql = "INSERT INTO	wcf".WCF_N."_template
					(packageID, templateName, lastModificationTime, application)
			VALUES		(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($files as $file) {
			if (isset($existingTemplates[$file])) {
				$updateTemplateIDs[] = $existingTemplates[$file];
				continue;
			}
			
			$statement->execute(array(
				$packageID,
				$file,
				TIME_NOW,
				$this->application
			));
		}
		
		if (!empty($updateTemplateIDs)) {
			// update old templates
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('templateID IN (?)', array($updateTemplateIDs));
			
			$sql = "UPDATE	wcf".WCF_N."_template
				SET	lastModificationTime = ?
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array_merge(array(TIME_NOW), $conditionBuilder->getParameters()));
		}
	}
}
