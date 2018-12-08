<?php
namespace wcf\system\package\plugin;
use wcf\data\acl\option\ACLOptionEditor;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes acl options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class ACLOptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin implements IIdempotentPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = ACLOptionEditor::class;
	
	/**
	 * list of loaded acl object type ids sorted by their option type name
	 * @var	integer[]
	 */
	protected $optionTypeIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public $tableName = 'acl_option';
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'option';
	
	/**
	 * @inheritDoc
	 */
	protected function deleteItems(\DOMXPath $xpath) {
		// delete options
		$elements = $xpath->query('/ns:data/ns:delete/ns:option');
		$options = [];
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$options[] = [
				'name' => $element->getAttribute('name'),
				'objectType' => $element->getElementsByTagName('objecttype')->item(0)->nodeValue
			];
		}
		
		if (!empty($options)) {
			$sql = "DELETE FROM	" . $this->application . WCF_N . "_" . $this->tableName. "
				WHERE		optionName = ?
						AND objectTypeID = ?
						AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($options as $option) {
				$statement->execute([
					$option['name'],
					$this->getObjectTypeID($option['objectType']),
					$this->installation->getPackageID()
				]);
			}
		}
		
		// delete categories
		$elements = $xpath->query('/ns:data/ns:delete/ns:optioncategory');
		$categories = [];
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$categories[] = [
				'name' => $element->getAttribute('name'),
				'objectType' => $element->getElementsByTagName('objecttype')->item(0)->nodeValue
			];
		}
		
		if (!empty($categories)) {
			// delete options for given categories
			$sql = "DELETE FROM	" . $this->application . WCF_N . "_" . $this->tableName. "
				WHERE		categoryName = ?
						AND objectTypeID = ?
						AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($categories as $category) {
				$statement->execute([
					$category['name'],
					$this->getObjectTypeID($category['objectType']),
					$this->installation->getPackageID()
				]);
			}
			
			// delete categories
			$sql = "DELETE FROM	" . $this->application . WCF_N . "_" . $this->tableName. "_category
				WHERE		categoryName = ?
						AND objectTypeID = ?
						AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($categories as $category) {
				$statement->execute([
					$category['name'],
					$this->getObjectTypeID($category['objectType']),
					$this->installation->getPackageID()
				]);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function importCategories(\DOMXPath $xpath) {
		$elements = $xpath->query('/ns:data/ns:import/ns:categories/ns:category');
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$data = ['categoryName' => $element->getAttribute('name')];
			
			// get child elements
			$children = $xpath->query('child::*', $element);
			foreach ($children as $child) {
				$data[$child->tagName] = $child->nodeValue;
			}
			
			$this->saveCategory($data);
		}
	}
	
	/**
	 * @inheritDoc
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
		$statement->execute([
			$category['categoryName'],
			$objectTypeID,
			$this->installation->getPackageID()
		]);
		$row = $statement->fetchArray();
		if (!$row) {
			// insert new category
			$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."_category
						(packageID, objectTypeID, categoryName)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$this->installation->getPackageID(),
				$objectTypeID,
				$category['categoryName']
			]);
		}
	}
	
	/**
	 * Imports options.
	 * 
	 * @param	\DOMXPath	$xpath
	 * @throws	SystemException
	 */
	protected function importOptions(\DOMXPath $xpath) {
		$elements = $xpath->query('/ns:data/ns:import/ns:options/ns:option');
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$data = [];
			$children = $xpath->query('child::*', $element);
			foreach ($children as $child) {
				$data[$child->tagName] = $child->nodeValue;
			}
			
			$objectTypeID = $this->getObjectTypeID($data['objecttype']);
			
			// validate category name
			if (isset($data['categoryname'])) {
				$sql = "SELECT	COUNT(categoryID)
					FROM	wcf".WCF_N."_".$this->tableName."_category
					WHERE	categoryName = ?
						AND objectTypeID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					$data['categoryname'],
					$objectTypeID
				]);
				
				if (!$statement->fetchSingleColumn()) {
					throw new SystemException("unknown category '".$data['categoryname']."' for acl object type '".$data['objecttype']."' given");
				}
			}
			
			$data = [
				'categoryName' => isset($data['categoryname']) ? $data['categoryname'] : '',
				'optionName' => $element->getAttribute('name'),
				'objectTypeID' => $objectTypeID
			];
			
			// check for option existence
			$sql = "SELECT	optionID
				FROM	wcf".WCF_N."_".$this->tableName."
				WHERE	optionName = ?
					AND objectTypeID = ?
					AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$data['optionName'],
				$data['objectTypeID'],
				$this->installation->getPackageID()
			]);
			$row = $statement->fetchArray();
			if (!$row) {
				$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."
							(packageID, objectTypeID, optionName, categoryName)
					VALUES		(?, ?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					$this->installation->getPackageID(),
					$data['objectTypeID'],
					$data['optionName'],
					$data['categoryName']
				]);
			}
			else {
				$sql = "UPDATE	wcf".WCF_N."_".$this->tableName."
					SET	categoryName = ?
					WHERE	optionID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					$data['categoryName'],
					$row['optionID']
				]);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function saveOption($option, $categoryName, $existingOptionID = 0) {
		// does nothing
	}
	
	/**
	 * Returns the object type id of the acl option type with the given name
	 * or throws a SystemException if no such option type exists.
	 * 
	 * @param	string		$optionType
	 * @return	integer
	 * @throws	SystemException
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
			$statement->execute([$optionType]);
			$objectTypeID = $statement->fetchSingleColumn();
			if ($objectTypeID === false) {
				throw new SystemException("unknown object type '".$optionType."' given");
			}
			
			$this->optionTypeIDs[$optionType] = $objectTypeID;
		}
		
		return $this->optionTypeIDs[$optionType];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.0
	 */
	public static function getDefaultFilename() {
		return 'aclOption.xml';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getSyncDependencies() {
		return ['objectType'];
	}
}
