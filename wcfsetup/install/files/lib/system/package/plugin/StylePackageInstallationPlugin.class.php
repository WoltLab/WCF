<?php
namespace wcf\system\package\plugin;
use wcf\data\style\StyleEditor;
use wcf\data\style\StyleList;
use wcf\system\event\EventHandler;
use wcf\system\style\StyleHandler;

/**
 * Installs, updates and deletes styles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class StylePackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = StyleEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public function install() {
		parent::install();
		
		// extract style tar
		$filename = $this->installation->getArchive()->extractTar($this->instruction['value'], 'style_');
		
		// searches for non-tainted style for updating
		$styleEditor = StyleHandler::getInstance()->getStyleByName($this->installation->getPackageName(), false);
		
		// import style
		$style = StyleEditor::import($filename, $this->installation->getPackageID(), $styleEditor, !PACKAGE_ID);
		
		// set style as default
		if (isset($this->instruction['attributes']['default'])) {
			$style->setAsDefault();
		}
		
		// remove tmp file
		@unlink($filename);
	}
	
	/**
	 * @inheritDoc
	 */
	public function uninstall() {
		// call uninstall event
		EventHandler::getInstance()->fireAction($this, 'uninstall');
		
		// get all style of this package
		$isDefault = false;
		$styleList = new StyleList();
		$styleList->getConditionBuilder()->add("packageID = ?", [$this->installation->getPackageID()]);
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
			$style = $styleList->getSingleObject();
			
			if ($style !== null) {
				(new StyleEditor($style))->setAsDefault();
			}
		}
	}
}
