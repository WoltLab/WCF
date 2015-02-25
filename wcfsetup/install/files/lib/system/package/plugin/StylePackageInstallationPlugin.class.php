<?php
namespace wcf\system\package\plugin;
use wcf\data\style\StyleEditor;
use wcf\data\style\StyleList;
use wcf\system\event\EventHandler;

/**
 * Installs, updates and deletes styles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class StylePackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\style\StyleEditor';
	
	/** 
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		// extract style tar
		$filename = $this->installation->getArchive()->extractTar($this->instruction['value'], 'style_');
		
		// import style
		$style = StyleEditor::import($filename, $this->installation->getPackageID());
		
		// set style as default
		if (isset($this->instruction['attributes']['default'])) {
			$style->setAsDefault();
		}
		
		// remove tmp file
		@unlink($filename);
	}
	
	/** 
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// call uninstall event
		EventHandler::getInstance()->fireAction($this, 'uninstall');
		
		// get all style of this package
		$isDefault = false;
		$styleList = new StyleList();
		$styleList->getConditionBuilder()->add("packageID = ?", array($this->installation->getPackageID()));
		$styleList->readObjects();
		
		foreach ($styleList->getObjects() as $style) {
			$styleEditor = new StyleEditor($style);
			$styleEditor->delete();
			
			$isDefault = $isDefault || $style->isDefault;
		}
		
		// default style deleted
		if ($isDefault) {
			$styleList = new StyleList();
			$styleList->sqlOrderBy = 'style.styleID ASC';
			$styleList->sqlLimit = 1;
			$styleList->readObjects();
			$styles = $styleList->getObjects();
			
			if (!empty($styles)) {
				$styleEditor = new StyleEditor(current($styles));
				$styleEditor->setAsDefault();
			}
		}
	}
}
