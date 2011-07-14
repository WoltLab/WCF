<?php
namespace wcf\system\package\plugin;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * This PIP executes an individual php script.
 *
 * @author 	Benjamin Kunz
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class ScriptPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();

		// get installation path of package
		$sql = "SELECT	packageDir
			FROM	wcf".WCF_N."_package
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		$packageDir = $statement->fetchArray();
		$packageDir = $packageDir['packageDir'];
		
		// get relative path of script
		$path = FileUtil::getRealPath(WCF_DIR.$packageDir);
		
		// run script
		$this->run($path.$this->instruction['value']);
		
		// delete script
		if (@unlink($path.$this->instruction['value'])) {
			// delete file log entry
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_file_log
				WHERE		packageID = ?
						AND filename = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->installation->getPackageID(),
				$this->instruction['value']
			));
		}
	}
	
	private function run($scriptPath) {
		include($scriptPath);
	}
	
	/**
	 * Returns false. Scripts can't be uninstalled.
	 *
	 * @return 	boolean 	false
	 */
	public function hasUninstall() {
		return false;
	}
	
	/**
	 * Does nothing.
	 */
	public function uninstall() {}
}
