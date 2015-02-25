<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes acl options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class ACLOptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\acl\option\ACLOptionEditor';
	
	/**
	 * list of loaded acl object type ids sorted by their option type name
	 * @var	array<integer>
	 */
	protected $optionTypeIDs = array();
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'acl_option';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'option';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractOptionPackageInstallationPlugin::importCategories()
	 */
	protected function importCategories(\DOMXPath $xpath) {
		$elements = $xpath->query('/ns:data/ns:import/ns:categories/ns:category');
		foreach ($elements as $element) {
			$data = array('categoryName' => $element->getAttribute('name'));
				
			// get child elements
			$children = $xpath->query('child::*', $element);
			foreach ($children as $child) {
				$data[$child->tagName] = $child->nodeValue;
			}
			
			$this->saveCategory($data);
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractOptionPackageInstallationPlugin::saveCategory()
	 */
	protected function saveCategory($category) {
		$objectTypeID = $this->getObjectTypeID($category['objecttype']);
		
		// search existing category
		$sql = "SELECT	categoryID
			FROM	wcf".WCF_N."_".$this->tableName."_category
			WHERE	categoryName = ?
				AND objectTypeID = ?
				AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$category['categoryName'],
			$objectTypeID,
			$this->installation->getPackageID()
		));
		$row = $statement->fetchArray();
		if (!$row) {
			// insert new category
			$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."_category
						(packageID, objectTypeID, categoryName)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->installation->getPackageID(),
				$objectTypeID,
				$category['categoryName']
			));
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
			
			$objectTypeID = $this->getObjectTypeID($data['objecttype']);
			
			// validate category name
			if (isset($data['categoryname'])) {
				$sql = "SELECT	COUNT(categoryID) AS count
					FROM	wcf".WCF_N."_".$this->tableName."_category
					WHERE	categoryName = ?
						AND objectTypeID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$data['categoryname'],
					$objectTypeID
				));
				$row = $statement->fetchArray();
				if (!$row) {
					throw new SystemException("unknown category '".$data['categoryname']."' for acl object type '".$data['objecttype']."' given");
				}
			}
			
			$data = array(
				'categoryName' => (isset($data['categoryname'])) ? $data['categoryname'] : '',
				'optionName' => $element->getAttribute('name'),
				'objectTypeID' => $objectTypeID
			);
			
			// check for option existence
			$sql = "SELECT	optionID
				FROM	wcf".WCF_N."_".$this->tableName."
				WHERE	optionName = ?
					AND objectTypeID = ?
					AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$data['optionName'],
				$data['objectTypeID'],
				$this->installation->getPackageID()
			));
			$row = $statement->fetchArray();
			if (!$row) {
				$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."
							(packageID, objectTypeID, optionName, categoryName)
					VALUES		(?, ?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$this->installation->getPackageID(),
					$data['objectTypeID'],
					$data['optionName'],
					$data['categoryName']
				));
			}
			else {
				$sql = "UPDATE	wcf".WCF_N."_".$this->tableName."
					SET	categoryName = ?
					WHERE	optionID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$data['categoryName'],
					$row['optionID']
				));
			}
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractOptionPackageInstallationPlugin::saveOption()
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		/* Does nothing */
	}
	
	/**
	 * Returns the object type id of the acl option type with the given name
	 * or throws a SystemException if no such option type exists.
	 * 
	 * @param	string		$optionType
	 * @return	integer
	 */
	protected function getObjectTypeID($optionType) {
		if (!isset($this->optionTypeIDs[$optionType])) {
			$sql = "SELECT	objectTypeID
				FROM	wcf".WCF_N."_object_type
				WHERE	objectType = ?
					AND definitionID IN (
						SELECT	definitionID
						FROM	wcf".WCF_N."_object_type_definition
						WHERE	definitionName = 'com.woltlab.wcf.acl'
					)";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute(array($optionType));
			$row = $statement->fetchArray();
			if (!$row) {
				throw new SystemException("unknown object type '".$optionType."' given");
			}
			
			$this->optionTypeIDs[$optionType] = $row['objectTypeID'];
		}
		
		return $this->optionTypeIDs[$optionType];
	}
}
