<?php
namespace wcf\system\package\plugin;
use wcf\data\style\StyleEditor;
use wcf\data\style\StyleList;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes style attributes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class StyleAttributesPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * list of updated or new style variables
	 * @var	array
	 */	
	protected $styleVariables = array();
	
	/**
	 * @see wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'style_variable_to_attribute';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */
	public $tagName = 'styleattribute';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND cssSelector = ?
					AND attributeName = ?
					AND variableName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($items as $item) {
			$statement->execute(array(
				$this->installation->getPackageID(),
				$item['elements']['selector'],
				$item['elements']['name'],
				$item['elements']['value']
			));
		}
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		return array(
			'cssSelector' => $data['elements']['selector'],
			'attributeName' => $data['elements']['name'],
			'variableName' => $data['elements']['value']
		);
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		return null;
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::import()
	 */	
	protected function import(array $row, array $data) {
		$this->styleVariables[] = $data;
	}
	
	/**
	 * It is not possible to properly update and insert values without
	 * spamming loads of queries for each import, thus delete all
	 * matching variables first and insert them afterwards.
	 * 
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::postImport()
	 */
	protected function postImport() {
		if (!count($this->styleVariables)) return;
		
		// delete items first
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND cssSelector = ?
					AND attributeName = ?
					AND variableName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($this->styleVariables as $variable) {
			$statement->execute(array(
				$this->installation->getPackageID(),
				$variable['cssSelector'],
				$variable['attributeName'],
				$variable['variableName']
			));
		}
		
		// insert items
		$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."
					(packageID, cssSelector, attributeName, variableName)
			VALUES		(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($this->styleVariables as $variable) {
			$statement->execute(array(
				$this->installation->getPackageID(),
				$variable['cssSelector'],
				$variable['attributeName'],
				$variable['variableName']
			));
		}
	}
	
	/**
	 * @see	 wcf\system\package\plugin\PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		parent::uninstall();
		
		$this->cleanup();
	}
	
	/**
	 * Updates styles files of all styles.
	 */
	protected function cleanup() {
		// get all styles
		$styleList = new StyleList();
		$styleList->sqlLimit = 0;
		$styleList->readObjects();
		
		foreach ($styleList->getObjects() as $style) {
			$styleEditor = new StyleEditor($style);
			$style->writeStyleFile();
		}
	}
}
