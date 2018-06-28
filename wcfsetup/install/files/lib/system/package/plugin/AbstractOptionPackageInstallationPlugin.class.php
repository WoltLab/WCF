<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract implementation of a package installation plugin for options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
abstract class AbstractOptionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::install()
	 */
	public function install() {
		AbstractPackageInstallationPlugin::install();
		
		$xml = $this->getXML($this->instruction['value']);
		$xpath = $xml->xpath();
		
		if ($this->installation->getAction() == 'update') {
			// handle delete first
			$this->deleteItems($xpath);
		}
		
		// import or update categories
		$this->importCategories($xpath);
		
		// import or update options
		$this->importOptions($xpath);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::deleteItems()
	 */
	protected function deleteItems(\DOMXPath $xpath) {
		// delete options
		$elements = $xpath->query('/ns:data/ns:delete/ns:option');
		$options = array();
		foreach ($elements as $element) {
			$options[] = $element->getAttribute('name');
		}
		
		if (!empty($options)) {
			$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."
				WHERE		optionName = ?
				AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($options as $option) {
				$statement->execute(array(
					$option,
					$this->installation->getPackageID()
				));
			}
		}
		
		// delete categories
		$elements = $xpath->query('/ns:data/ns:delete/ns:optioncategory');
		$categories = array();
		foreach ($elements as $element) {
			$categories[] = $element->getAttribute('name');
		}
		
		if (!empty($categories)) {
			// delete options for given categories
			$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."
				WHERE		categoryName = ?
						AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($categories as $category) {
				$statement->execute(array(
					$category,
					$this->installation->getPackageID()
				));
			}
			
			// delete categories
			$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."_category
				WHERE		categoryName = ?
				AND		packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($categories as $category) {
				$statement->execute(array(
					$category,
					$this->installation->getPackageID()
				));
			}
		}
	}
	
	/**
	 * Imports option categories.
	 * 
	 * @param	\DOMXPath	$xpath
	 */
	protected function importCategories(\DOMXPath $xpath) {
		$elements = $xpath->query('/ns:data/ns:import/ns:categories/ns:category');
		foreach ($elements as $element) {
			$data = array();
			
			// get child elements
			$children = $xpath->query('child::*', $element);
			foreach ($children as $child) {
				$data[$child->tagName] = $child->nodeValue;
			}
			
			// build data block with defaults
			$data = array(
				'categoryName' => $element->getAttribute('name'),
				'options' => (isset($data['options'])) ? $data['options'] : '',
				'parentCategoryName' => (isset($data['parent'])) ? $data['parent'] : '',
				'permissions' => (isset($data['permissions'])) ? $data['permissions'] : '',
				'showOrder' => (isset($data['showorder'])) ? intval($data['showorder']) : null
			);
			
			// adjust show order
			if ($data['showOrder'] !== null || $this->installation->getAction() != 'update') {
				$data['showOrder'] = $this->getShowOrder($data['showOrder'], $data['parentCategoryName'], 'parentCategoryName', '_category');
			}
			
			// validate parent
			if (!empty($data['parentCategoryName'])) {
				$sql = "SELECT	COUNT(categoryID) AS count
					FROM	".$this->application.WCF_N."_".$this->tableName."_category
					WHERE	categoryName = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array($data['parentCategoryName']));
				$row = $statement->fetchArray();
				
				if (!$row['count']) {
					throw new SystemException("Unable to find parent 'option category' with name '".$data['parentCategoryName']."' for category with name '".$data['categoryName']."'.");
				}
			}
			
			// save category
			$this->saveCategory($data);
		}
	}
	
	/**
	 * Imports options.
	 * 
	 * @param	\DOMXPath	$xpath
	 */
	protected function importOptions(\DOMXPath $xpath) {
		$elements = $xpath->query('/ns:data/ns:import/ns:options/ns:option');
		foreach ($elements as $element) {
			$data = array();
			$children = $xpath->query('child::*', $element);
			foreach ($children as $child) {
				$data[$child->tagName] = $child->nodeValue;
			}
			
			$data['name'] = $element->getAttribute('name');
			
			if (!preg_match("/^[\w\-\.]+$/", $data['name'])) {
				$matches = array();
				preg_match_all("/(\W)/", $data['name'], $matches);
				throw new SystemException("The option '".$data['name']."' has at least one non-alphanumeric character (underscore is permitted): (".implode("), ( ", $matches[1]).").");
			}
			
			$this->saveOption($data, $data['categoryname']);
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::hasUninstall()
	 */
	public function hasUninstall() {
		$hasUninstallOptions = parent::hasUninstall();
		$sql = "SELECT	COUNT(categoryID) AS count
			FROM	".$this->application.WCF_N."_".$this->tableName."_category
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		$categoryCount = $statement->fetchArray();
		return ($hasUninstallOptions || $categoryCount['count'] > 0);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// delete options
		parent::uninstall();
		
		// delete categories
		$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."_category
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
	}
	
	/**
	 * Installs option categories.
	 * 
	 * @param	array		$category
	 */
	protected function saveCategory($category) {
		// search existing category
		$sql = "SELECT	categoryID, packageID
			FROM	".$this->application.WCF_N."_".$this->tableName."_category
			WHERE	categoryName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$category['categoryName']
		));
		$row = $statement->fetchArray();
		if (empty($row['categoryID'])) {
			// insert new category
			$sql = "INSERT INTO	".$this->application.WCF_N."_".$this->tableName."_category
						(packageID, categoryName, parentCategoryName, permissions,
						options".($category['showOrder'] !== null ? ",showOrder" : "").")
				VALUES		(?, ?, ?, ?, ?".($category['showOrder'] !== null ? ", ?" : "").")";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			$data = array(
				$this->installation->getPackageID(),
				$category['categoryName'],
				$category['parentCategoryName'],
				$category['permissions'],
				$category['options']
			);
			if ($category['showOrder'] !== null) $data[] = $category['showOrder'];
			
			$statement->execute($data);
		}
		else {
			if ($row['packageID'] != $this->installation->getPackageID()) {
				throw new SystemException("Cannot override existing category '".$category['categoryName']."'");
			}
			
			// update existing category
			$sql = "UPDATE	".$this->application.WCF_N."_".$this->tableName."_category
				SET	parentCategoryName = ?,
					permissions = ?,
					options = ?
					".($category['showOrder'] !== null ? ", showOrder = ?" : "")."
				WHERE	categoryID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			$data = array(
				$category['parentCategoryName'],
				$category['permissions'],
				$category['options']
			);
			if ($category['showOrder'] !== null) $data[] = $category['showOrder'];
			$data[] = $row['categoryID'];
			
			$statement->execute($data);
		}
	}
	
	/**
	 * Installs options.
	 * 
	 * @param	array		$option
	 * @param	string		$categoryName
	 * @param	integer		$existingOptionID
	 */
	abstract protected function saveOption($option, $categoryName, $existingOptionID = 0);
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) { }
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) { }
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) { }
}
