<?php
namespace wcf\system\package\plugin;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\XML;

/**
 * Abstract implementation of a package installation plugin using a XML file.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
abstract class AbstractXMLPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * object editor class name
	 * @var	string
	 */
	public $className = '';
	
	/**
	 * xml tag name, e.g. 'acpmenuitem'
	 * @var	string
	 */
	public $tagName = '';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractPackageInstallationPlugin::install()
	 */
	public function __construct(PackageInstallationDispatcher $installation, $instruction = array()) {
		parent::__construct($installation, $instruction);
		
		// autoset 'tableName' property
		if (empty($this->tableName) && !empty($this->className)) {
			$this->tableName = call_user_func(array($this->className, 'getDatabaseTableAlias'));
		}
		
		// autoset 'tagName' property
		if (empty($this->tagName) && !empty($this->tableName)) {
			$this->tagName = str_replace('_', '', $this->tableName);
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		// get xml
		$xml = $this->getXML($this->instruction['value']);
		$xpath = $xml->xpath();
		
		// handle delete first
		if ($this->installation->getAction() == 'update') {
			$this->deleteItems($xpath);
		}
		
		// handle import
		$this->importItems($xpath);
		
		// execute cleanup
		$this->cleanup();
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		parent::uninstall();
		
		// execute cleanup
		$this->cleanup();
	}
	
	/**
	 * Deletes items.
	 * 
	 * @param	\DOMXPath	$xpath
	 */
	protected function deleteItems(\DOMXPath $xpath) {
		$elements = $xpath->query('/ns:data/ns:delete/ns:'.$this->tagName);
		$items = array();
		foreach ($elements as $element) {
			$data = array(
				'attributes' => array(),
				'elements' => array(),
				'value' => $element->nodeValue
			);
			
			// get attributes
			$attributes = $xpath->query('attribute::*', $element);
			foreach ($attributes as $attribute) {
				$data['attributes'][$attribute->name] = $attribute->value;
			}
			
			// get child elements
			$childNodes = $xpath->query('child::*', $element);
			foreach ($childNodes as $childNode) {
				$data['elements'][$childNode->nodeName] = $childNode->nodeValue;
			}
			
			$items[] = $data;
		}
		
		// delete items
		if (!empty($items)) {
			$this->handleDelete($items);
		}
	}
	
	/**
	 * Imports or updates items.
	 * 
	 * @param	\DOMXPath	$xpath
	 */
	protected function importItems(\DOMXPath $xpath) {
		$elements = $xpath->query('/ns:data/ns:import/ns:'.$this->tagName);
		foreach ($elements as $element) {
			$data = array(
				'attributes' => array(),
				'elements' => array(),
				'nodeValue' => ''
			);
			
			// fetch attributes
			$attributes = $xpath->query('attribute::*', $element);
			foreach ($attributes as $attribute) {
				$data['attributes'][$attribute->name] = $attribute->value;
			}
			
			// fetch child elements
			$items = $xpath->query('child::*', $element);
			foreach ($items as $item) {
				$this->getElement($xpath, $data['elements'], $item);
			}
			
			// include node value if item does not contain any child elements (eg. pip)
			if (empty($data['elements'])) {
				$data['nodeValue'] = $element->nodeValue;
			}
			
			// map element data to database fields
			$data = $this->prepareImport($data);
			
			// validate item data
			$this->validateImport($data);
			
			// try to find an existing item for updating
			$sqlData = $this->findExistingItem($data);
			
			// handle items which do not support updating (e.g. cronjobs)
			if ($sqlData === null) $row = false;
			else {
				$statement = WCF::getDB()->prepareStatement($sqlData['sql']);
				$statement->execute($sqlData['parameters']);
				$row = $statement->fetchArray();
			}
			
			// ensure a valid parameter for import()
			if ($row === false) $row = array();
			
			// import items
			$this->import($row, $data);
		}
		
		// fire after import
		$this->postImport();
	}
	
	/**
	 * Sets element value from XPath.
	 * 
	 * @param	\DOMXPath	$xpath
	 * @param	array		$elements
	 * @param	\DOMElement	$element
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$elements[$element->tagName] = $element->nodeValue;
	}
	
	/**
	 * Inserts or updates new items.
	 * 
	 * @param	array		$row
	 * @param	array		$data
	 * @return	\wcf\data\IStorableObject
	 */
	protected function import(array $row, array $data) {
		if (empty($row)) {
			// create new item
			$this->prepareCreate($data);
			
			return call_user_func(array($this->className, 'create'), $data);
		}
		else {
			// update existing item
			$baseClass = call_user_func(array($this->className, 'getBaseClass'));
			
			$itemEditor = new $this->className(new $baseClass(null, $row));
			$itemEditor->update($data);
			
			return $itemEditor;
		}
	}
	
	/**
	 * Executed after all items would have been imported, use this hook if you've
	 * overwritten import() to disable insert/update.
	 */
	protected function postImport() { }
	
	/**
	 * Deletes the given items.
	 * 
	 * @param	array	$items
	 */
	abstract protected function handleDelete(array $items);
	
	/**
	 * Prepares import, use this to map xml tags and attributes
	 * to their corresponding database fields.
	 * 
	 * @param	array	$data
	 * @return	array
	 */
	abstract protected function prepareImport(array $data);
	
	/**
	 * Validates given item, e.g. checking for invalid values. If validation
	 * fails you should throw an exception.
	 * 
	 * @param	array	$data
	 */
	protected function validateImport(array $data) { }
	
	/**
	 * Find an existing item for updating, should return sql query.
	 * 
	 * @param	array	$data
	 * @return	array
	 */
	abstract protected function findExistingItem(array $data);
	
	/**
	 * Append additional fields which are not to be updated if a corresponding
	 * item exists but are required for creation.
	 * 
	 * Attention: $data is passed by reference
	 * 
	 * @param	array	$data
	 */
	protected function prepareCreate(array &$data) {
		$data['packageID'] = $this->installation->getPackageID();
	}
	
	/**
	 * Triggered after executing all delete and/or import actions.
	 */
	protected function cleanup() { }
	
	/**
	 * Loads the xml file into a string and returns this string.
	 * 
	 * @param	string		$filename
	 * @return	XML		$xml
	 */
	protected function getXML($filename = '') {
		if (empty($filename)) {
			$filename = $this->instruction['value'];
		}
		
		// Search the xml-file in the package archive.
		// Abort installation in case no file was found.
		if (($fileIndex = $this->installation->getArchive()->getTar()->getIndexByFilename($filename)) === false) {
			throw new SystemException("xml file '".$filename."' not found in '".$this->installation->getArchive()->getArchive()."'");
		}
		
		// Extract acpmenu file and parse XML
		$xml = new XML();
		$tmpFile = FileUtil::getTemporaryFilename('xml_');
		try {
			$this->installation->getArchive()->getTar()->extract($fileIndex, $tmpFile);
			$xml->load($tmpFile);
		}
		catch (\Exception $e) { // bugfix to avoid file caching problems
			try {
				$this->installation->getArchive()->getTar()->extract($fileIndex, $tmpFile);
				$xml->load($tmpFile);
			}
			catch (\Exception $e) {
				$this->installation->getArchive()->getTar()->extract($fileIndex, $tmpFile);
				$xml->load($tmpFile);
			}
		}
		
		@unlink($tmpFile);
		return $xml;
	}
	
	/**
	 * Returns the show order value.
	 * 
	 * @param	integer		$showOrder
	 * @param	string		$parentName
	 * @param	string		$columnName
	 * @param	string		$tableNameExtension
	 * @return	integer
	 */
	protected function getShowOrder($showOrder, $parentName = null, $columnName = null, $tableNameExtension = '') {
		if ($showOrder === null) {
			// get greatest showOrder value
			$conditions = new PreparedStatementConditionBuilder();
			if ($columnName !== null) $conditions->add($columnName." = ?", array($parentName));
			
			$sql = "SELECT	MAX(showOrder) AS showOrder
				FROM	".$this->application.WCF_N."_".$this->tableName.$tableNameExtension."
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$maxShowOrder = $statement->fetchArray();
			return (!$maxShowOrder) ? 1 : ($maxShowOrder['showOrder'] + 1);
		}
		else {
			// increase all showOrder values which are >= $showOrder
			$sql = "UPDATE	".$this->application.WCF_N."_".$this->tableName.$tableNameExtension."
				SET	showOrder = showOrder + 1
				WHERE	showOrder >= ?
				".($columnName !== null ? "AND ".$columnName." = ?" : "");
			$statement = WCF::getDB()->prepareStatement($sql);
			
			$data = array($showOrder);
			if ($columnName !== null) $data[] = $parentName;
			
			$statement->execute($data);
			
			// return the wanted showOrder level
			return $showOrder;
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::isValid()
	 */
	public static function isValid(PackageArchive $archive, $instruction) {
		if (preg_match('~\.xml$~', $instruction)) {
			// check if file actually exists
			try {
				if ($archive->getTar()->getIndexByFilename($instruction) === false) {
					return false;
				}
			}
			catch (\SystemException $e) {
				return false;
			}
			
			return true;
		}
		
		return false;
	}
}
