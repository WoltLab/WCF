<?php
namespace wcf\system\package\plugin;
use wcf\data\bbcode\attribute\BBCodeAttributeEditor;
use wcf\data\package\PackageCache;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes bbcodes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category	Community Framework
 */
class BBCodePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\bbcode\BBCodeEditor';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'bbcode';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */
	public $tagName = 'bbcode';
	
	/**
	 * list of attributes per bbcode id
	 * @var	array<array>
	 */
	protected $attributes = array();
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		bbcodeTag = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$item['attributes']['name'],
				$this->installation->getPackageID()
			));
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::getElement()
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		// read pages
		if ($element->tagName == 'attributes') {
			$nodeValue = array();
			
			$attributes = $xpath->query('child::*', $element);
			foreach ($attributes as $attribute) {
				$attributeNo = $attribute->getAttribute('name');
				$nodeValue[$attributeNo] = array();
				
				$attributeValues = $xpath->query('child::*', $attribute);
				foreach ($attributeValues as $attributeValue) {
					$nodeValue[$attributeNo][$attributeValue->tagName] = $attributeValue->nodeValue;
				}
			}
		}
		
		$elements[$element->tagName] = $nodeValue;
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$data = array(
			'bbcodeTag' => mb_strtolower(StringUtil::trim($data['attributes']['name'])),
			'htmlOpen' => (!empty($data['elements']['htmlopen']) ? $data['elements']['htmlopen'] : ''),
			'htmlClose' => (!empty($data['elements']['htmlclose']) ? $data['elements']['htmlclose'] : ''),
			'allowedChildren' => (!empty($data['elements']['allowedchildren']) ? $data['elements']['allowedchildren'] : 'all'),
			'wysiwygIcon' => (!empty($data['elements']['wysiwygicon']) ? $data['elements']['wysiwygicon'] : ''),
			'attributes' => (isset($data['elements']['attributes']) ? $data['elements']['attributes'] : array()),
			'className' => (!empty($data['elements']['classname']) ? $data['elements']['classname'] : ''),
			'isSourceCode' => (!empty($data['elements']['sourcecode']) ? 1 : 0),
			'buttonLabel' => (isset($data['elements']['buttonlabel']) ? $data['elements']['buttonlabel'] : ''),
			'originIsSystem' => 1
		);
		
		if ($data['wysiwygIcon'] && $data['buttonLabel']) {
			$data['showButton'] = 1;
		}
		
		return $data;
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::validateImport()
	 */
	protected function validateImport(array $data) {
		if ($data['bbcodeTag'] == 'all' || $data['bbcodeTag'] == 'none') {
			throw new SystemException("BBCodes can't be called 'all' or 'none'");
		}
		
		// check if bbcode tag already exists
		$sqlData = $this->findExistingItem($data);
		$statement = WCF::getDB()->prepareStatement($sqlData['sql']);
		$statement->execute($sqlData['parameters']);
		$row = $statement->fetchArray();
		if ($row && $row['packageID'] != $this->installation->getPackageID()) {
			$package = PackageCache::getInstance()->getPackage($row['packageID']);
			throw new SystemException("BBCode '" . $data['bbcodeTag'] . "' is already provided by '" . $package . "' ('" . $package->package . "').");
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	bbcodeTag = ?
				AND packageID = ?";
		$parameters = array(
			$data['bbcodeTag'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::import()
	 */
	protected function import(array $row, array $data) {
		// extract attributes
		$attributes = $data['attributes'];
		unset($data['attributes']);
		
		// import or update action
		$object = parent::import($row, $data);
		
		// store attributes for later import
		$this->attributes[$object->bbcodeID] = $attributes;
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::postImport()
	 */
	protected function postImport() {
		// clear attributes
		$sql = "DELETE FROM	wcf".WCF_N."_bbcode_attribute
			WHERE		bbcodeID IN (
						SELECT	bbcodeID
						FROM	wcf".WCF_N."_bbcode
						WHERE	packageID = ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		
		if (!empty($this->attributes)) {
			foreach ($this->attributes as $bbcodeID => $bbcodeAttributes) {
				foreach ($bbcodeAttributes as $attributeNo => $attribute) {
					BBCodeAttributeEditor::create(array(
						'bbcodeID' => $bbcodeID,
						'attributeNo' => $attributeNo,
						'attributeHtml' => (!empty($attribute['html']) ? $attribute['html'] : ''),
						'validationPattern' => (!empty($attribute['validationpattern']) ? $attribute['validationpattern'] : ''),
						'required' => (!empty($attribute['required']) ? $attribute['required'] : 0),
						'useText' => (!empty($attribute['usetext']) ? $attribute['usetext'] : 0),
					));
				}
			}
		}
	}
}
