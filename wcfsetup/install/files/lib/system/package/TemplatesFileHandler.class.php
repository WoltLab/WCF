<?php
namespace wcf\system\package;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * File handler implementation for the installation of template files.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package
 */
class TemplatesFileHandler extends ACPTemplatesFileHandler {
	/**
	 * @inheritDoc
	 */
	protected $supportsTemplateGroups = true;
	
	/**
	 * @inheritDoc
	 */
	protected $tableName = 'template';
	
	/**
	 * @inheritDoc
	 */
	public function logFiles(array $files) {
		$packageID = $this->packageInstallation->getPackageID();
		
		// remove file extension
		foreach ($files as &$file) {
			$file = substr($file, 0, -4);
		}
		unset($file);
		
		// get existing templates
		$existingTemplates = $updateTemplateIDs = [];
		$sql = "SELECT	templateName, templateID
			FROM	wcf".WCF_N."_template
			WHERE	packageID = ?
				AND application = ?
				AND templateGroupID IS NULL";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$packageID, $this->application]);
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
			
			$statement->execute([
				$packageID,
				$file,
				TIME_NOW,
				$this->application
			]);
		}
		
		if (!empty($updateTemplateIDs)) {
			// update old templates
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('templateID IN (?)', [$updateTemplateIDs]);
			
			$sql = "UPDATE	wcf".WCF_N."_template
				SET	lastModificationTime = ?
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array_merge([TIME_NOW], $conditionBuilder->getParameters()));
		}
	}
}
