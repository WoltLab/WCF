<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\io\Tar;
use wcf\system\package\ACPTemplatesFileHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * This PIP installs, updates or deletes by a package delivered ACP-templates.
 *
 * @author 	Benjamin Kunz
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class ACPTemplatesPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	public $tagName = 'acptemplates';
	public $tableName = 'acp_template';
	
	/**
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();

		// extract files.tar to temp folder
		$sourceFile = $this->installation->getArchive()->extractTar($this->instruction['value'], 'acptemplates_');
		
		// create file handler
		$fileHandler = new ACPTemplatesFileHandler($this->installation);
		
		// extract content of files.tar
		$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$this->installation->getPackage()->packageDir));
		
		try {
			$fileInstaller = $this->installation->extractFiles($packageDir.'acp/templates/', $sourceFile, $fileHandler);
		}
		catch (SystemException $e) {
			WCF::getTPL()->assign(array(
				'exception' => $e
			));
			WCF::getTPL()->display('packageInstallationFileInstallationFailed');
			exit;
		}
		
		// delete temporary sourceArchive
		@unlink($sourceFile);
	}
	
	/**
	 * @see PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// create ACP-templates list
		$templates = array();
		
		// get ACP-templates from log
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_acp_template
			WHERE 	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		while ($row = $statement->fetchArray()) {
			// store acp template with suffix (_$packageID)
			$templates[] = 'acp/templates/'.$row['templateName'].'.tpl';
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
?>
