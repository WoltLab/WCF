<?php
namespace wcf\system\package\plugin;
use wcf\data\package\PackageEditor;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;

/**
 * This PIP installs, updates or deletes the package icon of a package.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class PackageIconPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		// search sql files in package archive
		if (($fileIndex = $this->installation->getArchive()->getTar()->getIndexByFilename($this->instruction['value'])) === false) {
			throw new SystemException("Package icon '".$this->instruction['value']."' not found.");
		}
		
		// get extension
		$extension = substr($this->instruction['value'], strrpos($this->instruction['value'], '.'));
		
		// extract image
		$this->installation->getArchive()->getTar()->extract($fileIndex, WCF_DIR . 'icon/packages/' . $this->installation->getPackage()->packageID . $extension);
		
		// update package
		$packageEditor = new PackageEditor($this->installation->getPackage());
		$packageEditor->update(array(
			'packageIcon' => 'icon/packages/' . $packageEditor->packageID . $extension
		));
	}
	
	/**
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// call uninstall event
		EventHandler::getInstance()->fireAction($this, 'uninstall');
		
		$packageIcon = $this->installation->getPackage()->packageIcon;
		if (!empty($packageIcon)) {
			@unlink(WCF_DIR . $packageIcon);
		}
	}
}
