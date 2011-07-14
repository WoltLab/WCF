<?php
namespace wcf\system\package\plugin;
use wcf\data\style\StyleEditor;
use wcf\data\style\StyleList;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes styles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class StylePackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @see AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\style\StyleEditor';
	
	/**
	 * @see AbstractXMLPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'style';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'style';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		// extract style tar
		$filename = $this->installation->getArchive()->extractTar($this->instructions['value'], 'style_');
		
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
	 * @see PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// call uninstall event
		EventHandler::fireAction($this, 'uninstall');
		
		// get all style of this package
		$isDefault = false;
		$styleList = new StyleList();
		$styleList->getConditionBuilder()->add("packageID = ?", array($this->installation->getPackageID()));
		$styleList->sqlLimit = 0;
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
			
			if (count($styles)) {
				$styleEditor = new StyleEditor($styles[0]);
				$styleEditor->setAsDefault();
			}
		}
	}
}
?>
