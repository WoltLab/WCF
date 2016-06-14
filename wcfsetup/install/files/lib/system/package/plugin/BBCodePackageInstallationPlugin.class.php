<?php
namespace wcf\system\package\plugin;
use wcf\data\bbcode\attribute\BBCodeAttributeEditor;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeEditor;
use wcf\data\package\PackageCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes bbcodes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 */
class BBCodePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = BBCodeEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tableName = 'bbcode';
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'bbcode';
	
	/**
	 * list of attributes per bbcode id
	 * @var	mixed[][]
	 */
	protected $attributes = [];
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		bbcodeTag = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$this->installation->getPackageID()
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		// read pages
		if ($element->tagName == 'attributes') {
			$nodeValue = [];
			
			$attributes = $xpath->query('child::*', $element);
			/** @var \DOMElement $attribute */
			foreach ($attributes as $attribute) {
				$attributeNo = $attribute->getAttribute('name');
				$nodeValue[$attributeNo] = [];
				
				$attributeValues = $xpath->query('child::*', $attribute);
				foreach ($attributeValues as $attributeValue) {
					$nodeValue[$attributeNo][$attributeValue->tagName] = $attributeValue->nodeValue;
				}
			}
		}
		
		$elements[$element->tagName] = $nodeValue;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$data = [
			'bbcodeTag' => mb_strtolower(StringUtil::trim($data['attributes']['name'])),
			'htmlOpen' => (!empty($data['elements']['htmlopen']) ? $data['elements']['htmlopen'] : ''),
			'htmlClose' => (!empty($data['elements']['htmlclose']) ? $data['elements']['htmlclose'] : ''),
			'wysiwygIcon' => (!empty($data['elements']['wysiwygicon']) ? $data['elements']['wysiwygicon'] : ''),
			'attributes' => (isset($data['elements']['attributes']) ? $data['elements']['attributes'] : []),
			'className' => (!empty($data['elements']['classname']) ? $data['elements']['classname'] : ''),
			'isBlockElement' => (!empty($data['elements']['isBlockElement']) ? 1 : 0),
			'isSourceCode' => (!empty($data['elements']['sourcecode']) ? 1 : 0),
			'buttonLabel' => (isset($data['elements']['buttonlabel']) ? $data['elements']['buttonlabel'] : ''),
			'originIsSystem' => 1
		];
		
		if ($data['wysiwygIcon'] && $data['buttonLabel']) {
			$data['showButton'] = 1;
		}
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateImport(array $data) {
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
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	bbcodeTag = ?
				AND packageID = ?";
		$parameters = [
			$data['bbcodeTag'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		// extract attributes
		$attributes = $data['attributes'];
		unset($data['attributes']);
		
		/** @var BBCode $bbcode */
		$bbcode = parent::import($row, $data);
		
		// store attributes for later import
		$this->attributes[$bbcode->bbcodeID] = $attributes;
		
		return $bbcode;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function postImport() {
		$condition = new PreparedStatementConditionBuilder();
		$condition->add('bbcodeID IN (?)', [array_keys($this->attributes)]);
		
		// clear attributes
		$sql = "DELETE FROM	wcf".WCF_N."_bbcode_attribute
			".$condition;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($condition->getParameters());
		
		if (!empty($this->attributes)) {
			foreach ($this->attributes as $bbcodeID => $bbcodeAttributes) {
				foreach ($bbcodeAttributes as $attributeNo => $attribute) {
					BBCodeAttributeEditor::create([
						'bbcodeID' => $bbcodeID,
						'attributeNo' => $attributeNo,
						'attributeHtml' => (!empty($attribute['html']) ? $attribute['html'] : ''),
						'validationPattern' => (!empty($attribute['validationpattern']) ? $attribute['validationpattern'] : ''),
						'required' => (!empty($attribute['required']) ? $attribute['required'] : 0),
						'useText' => (!empty($attribute['usetext']) ? $attribute['usetext'] : 0)
					]);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 * @since	3.0
	 */
	public static function getDefaultFilename() {
		return 'bbcode.xml';
	}
}
