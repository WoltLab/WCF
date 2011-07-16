<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\io\Tar;
use wcf\system\package\TemplatesFileHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * This PIP installs, updates or deletes by a package delivered templates.
 *
 * @author 	Benjamin Kunz
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class TemplatesPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	public $tableName = 'template';
	
	/**
	 * @see wcf\system\package\plugin\PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();

		// extract files.tar to temp folder
		$sourceFile = $this->installation->getArchive()->extractTar($this->instruction['value'], 'templates_');
		
		// create file handler
		$fileHandler = new TemplatesFileHandler($this->installation);
		
		// extract content of files.tar
		$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$this->installation->getPackage()->packageDir));
		
		$fileInstaller = $this->installation->extractFiles($packageDir.'templates/', $sourceFile, $fileHandler);
		
		// delete temporary sourceArchive
		@unlink($sourceFile);
	}
	
	/**
	 * Uninstalls the templates of this package.
	 */
	public function uninstall() {
		// create templates list
		$templates = array();
		
		// get templates from log
		$sql = "SELECT	templateName
			FROM	wcf".WCF_N."_template
			WHERE 	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		while ($row = $statement->fetchArray()) {
			$templates[] = 'templates/'.$row['templateName'].'.tpl';
		}
		
		if (count($templates) > 0) {
			// delete template files
			$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$this->installation->getPackage()->packageDir));
			$deleteEmptyDirectories = $this->installation->getPackage()->standalone;
			$this->installation->deleteFiles($packageDir, $templates, false, $deleteEmptyDirectories);
			
			// delete log entries
			parent::uninstall();
		}
	}
}
