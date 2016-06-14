<?php
namespace wcf\system\package\plugin;
use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\package\PackageArchive;
use wcf\system\package\TemplatesFileHandler;
use wcf\system\WCF;

/**
 * Installs, updates and deletes templates.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class TemplatePackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $tableName = 'template';
	
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
		$sourceFile = $this->installation->getArchive()->extractTar($this->instruction['value'], 'templates_');
		
		// create file handler
		$fileHandler = new TemplatesFileHandler($this->installation, $abbreviation);
		
		$this->installation->extractFiles($packageDir.'templates/', $sourceFile, $fileHandler);
		
		// delete temporary sourceArchive
		@unlink($sourceFile);
	}
	
	/**
	 * Uninstalls the templates of this package.
	 */
	public function uninstall() {
		// fetch templates from log
		$sql = "SELECT		template.templateName, template.application,
					template_group.templateGroupFolderName
			FROM		wcf".WCF_N."_template template
			LEFT JOIN	wcf".WCF_N."_template_group template_group
			ON		(template_group.templateGroupID = template.templateGroupID)
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
		
		$templates = [];
		while ($row = $statement->fetchArray()) {
			if (!isset($templates[$row['application']])) {
				$templates[$row['application']] = [];
			}
			
			$templates[$row['application']][] = 'templates/'.$row['templateGroupFolderName'].$row['templateName'].'.tpl';
		}
		
		foreach ($templates as $application => $templateNames) {
			/** @noinspection PhpUndefinedMethodInspection */
			$this->installation->deleteFiles(Application::getDirectory($application), $templateNames, false, $this->installation->getPackage()->isApplication);
			
			// delete log entries
			parent::uninstall();
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
	 * @since	3.0
	 */
	public static function getDefaultFilename() {
		return 'templates.tar';
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
