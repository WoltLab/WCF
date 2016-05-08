<?php
namespace wcf\system\package\plugin;
use wcf\data\box\Box;
use wcf\data\box\BoxEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Installs, updates and deletes boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category	Community Framework
 * @since	2.2
 */
class BoxPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = BoxEditor::class;
	
	/**
	 * box contents
	 * @var	array
	 */
	protected $content = [];
	
	/**
	 * list of element names which are not considered as additional data
	 * @var	string[]
	 */
	public static $reservedTags = ['boxType', 'content', 'cssClassName', 'name', 'objectType', 'position', 'showHeader', 'visibilityExceptions', 'visibleEverywhere'];
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'box';
	
	/**
	 * visibility exceptions per box
	 * @var	string[]
	 */
	public $visibilityExceptions = [];
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_box
			WHERE		identifier = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['identifier'],
				$this->installation->getPackageID()
			]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @inheritDoc
	 * @throws	SystemException
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		if ($element->tagName === 'name') {
			if (empty($element->getAttribute('language'))) {
				throw new SystemException("Missing required attribute 'language' for '" . $element->tagName . "' element (box '" . $element->parentNode->getAttribute('identifier') . "')");
			}
			
			// element can occur multiple times using the `language` attribute
			if (!isset($elements[$element->tagName])) $elements[$element->tagName] = [];
			
			$elements[$element->tagName][$element->getAttribute('language')] = $element->nodeValue;
		}
		else if ($element->tagName === 'content') {
			// content can occur multiple times using the `language` attribute
			if (!isset($elements['content'])) $elements['content'] = [];
			
			$children = [];
			/** @var \DOMElement $child */
			foreach ($xpath->query('child::*', $element) as $child) {
				$children[$child->tagName] = $child->nodeValue;
			}
			
			if (empty($children['title'])) {
				throw new SystemException("Expected non-empty child element 'title' for 'content' element (box '" . $element->parentNode->getAttribute('identifier') . "')");
			}
			
			$elements['content'][$element->getAttribute('language')] = [
				'content' => isset($children['content']) ? $children['content'] : '',
				'title' => $children['title']
			];
		}
		else if ($element->tagName === 'visibilityExceptions') {
			$elements['visibilityExceptions'] = [];
			/** @var \DOMElement $child */
			foreach ($xpath->query('child::*', $element) as $child) {
				$elements['visibilityExceptions'][] = $child->nodeValue;
			}
		}
		else {
			$elements[$element->tagName] = $nodeValue;
		}
	}
	
	/**
	 * @inheritDoc
	 * @throws	SystemException
	 */
	protected function prepareImport(array $data) {
		$content = [];
		$boxType = $data['elements']['boxType'];
		$objectTypeID = null;
		$identifier = $data['attributes']['identifier'];
		$isMultilingual = false;
		$position = $data['elements']['position'];
		
		if (!in_array($position, ['bottom', 'contentBottom', 'contentTop', 'footer', 'footerBoxes', 'headerBoxes', 'hero', 'sidebarLeft', 'sidebarRight', 'top'])) {
			throw new SystemException("Unknown box position '{$position}' for box '{$identifier}'");
		}
		
		$ignoreMissingContent = false;
		switch ($boxType) {
			case 'system':
				if (empty($data['elements']['objectType'])) {
					throw new SystemException("Missing required element 'objectType' for 'system'-type box '{$identifier}'");
				}
				
				$sql = "SELECT		objectTypeID
					FROM		wcf".WCF_N."_object_type object_type
					LEFT JOIN	wcf".WCF_N."_object_type_definition object_type_definition
					ON		(object_type_definition.definitionID = object_type.definitionID)
					WHERE		objectType = ?
							AND definitionName = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([$data['elements']['objectType'], 'com.woltlab.wcf.boxController']);
				$objectTypeID = $statement->fetchSingleColumn();
				if (!$objectTypeID) {
					throw new SystemException("Unknown object type '{$data['elements']['objectType']}' for 'system'-type box '{$identifier}'");
				}
				
				$ignoreMissingContent = true;
				
				// fallthrough
			
			case 'html':
			case 'text':
			case 'tpl':
				if (empty($data['elements']['content'])) {
					if ($ignoreMissingContent) {
						break;
					}
					
					throw new SystemException("Missing required 'content' element(s) for box '{$identifier}'");
				}
				
				if (count($data['elements']['content']) === 1) {
					if (!isset($data['elements']['content'][''])) {
						throw new SystemException("Expected one 'content' element without a 'language' attribute for box '{$identifier}'");
					}
				}
				else {
					$isMultilingual = true;
					
					if (isset($data['elements']['content'][''])) {
						throw new SystemException("Cannot mix 'content' elements with and without 'language' attribute for box '{$identifier}'");
					}
				}
				
				$content = $data['elements']['content'];
				
				break;
			
			default:
				throw new SystemException("Unknown type '{$boxType}' for box '{$identifier}");
				break;
		}
		
		if (!empty($data['elements']['visibilityExceptions'])) {
			$this->visibilityExceptions[$identifier] = $data['elements']['visibilityExceptions'];
		}
		
		$additionalData = [];
		foreach ($data['elements'] as $tagName => $nodeValue) {
			if (!in_array($tagName, self::$reservedTags)) {
				$additionalData[$tagName] = $nodeValue;
			}
		}
		
		return [
			'identifier' => $identifier,
			'content' => $content,
			'name' => $this->getI18nValues($data['elements']['name'], true),
			'boxType' => $boxType,
			'position' => $position,
			'showOrder' => $this->getItemOrder($position),
			'visibleEverywhere' => (!empty($data['elements']['visibleEverywhere'])) ? 1 : 0,
			'isMultilingual' => ($isMultilingual) ? '1' : '0',
			'cssClassName' => (!empty($data['elements']['cssClassName'])) ? $data['elements']['cssClassName'] : '',
			'showHeader' => (!empty($data['elements']['showHeader'])) ? 1 : 0,
			'originIsSystem' => 1,
			'objectTypeID' => $objectTypeID,
			'additionalData' => serialize($additionalData)
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_box
			WHERE	identifier = ?
				AND packageID = ?";
		$parameters = [
			$data['identifier'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * Returns the show order for a new item that will append it to the current
	 * menu or parent item.
	 *
	 * @param	string		$position	box position
	 * @return	integer
	 */
	protected function getItemOrder($position) {
		$sql = "SELECT  MAX(showOrder) AS showOrder
			FROM	wcf".WCF_N."_box
			WHERE   position = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([$position]);
		
		$row = $statement->fetchSingleRow();
		
		return (!$row['showOrder']) ? 1 : $row['showOrder'] + 1;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		// extract content
		$content = $data['content'];
		unset($data['content']);
		
		// updating boxes is only supported for 'system' type boxes, all other
		// types would potentially overwrite changes made by the user if updated
		if (!empty($row) && $row['boxType'] !== 'system') {
			$box = new Box(null, $row);
		}
		else {
			$box = parent::import($row, $data);
		}
		
		// store content for later import
		$this->content[$box->boxID] = $content;
		
		return $box;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function postImport() {
		if (!empty($this->content)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_box_content
							(boxID, languageID, title, content)
				VALUES			(?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($this->content as $boxID => $contentData) {
				foreach ($contentData as $languageCode => $content) {
					$languageID = null;
					if ($languageCode != '') {
						$language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
						if ($language === null) continue;
						
						$languageID = $language->languageID;
					}
					
					$statement->execute([
						$boxID,
						$languageID,
						$content['title'],
						isset($content['content']) ? $content['content'] : ''
					]);
				}
			}
			WCF::getDB()->commitTransaction();
		}
		
		if (empty($this->visibilityExceptions)) return;
		
		// get all boxes belonging to the identifiers
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("identifier IN (?)", [array_keys($this->visibilityExceptions)]);
		$conditions->add("packageID = ?", [$this->installation->getPackageID()]);
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_box
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$boxes = [];
		while ($box = $statement->fetchObject(Box::class)) {
			$boxes[$box->identifier] = $box;
		}
		
		// save visibility exceptions
		$sql = "DELETE FROM     wcf".WCF_N."_box_to_page
			WHERE           boxID = ?";
		$deleteStatement = WCF::getDB()->prepareStatement($sql);
		$sql = "INSERT IGNORE   wcf".WCF_N."_box_to_page
					(boxID, pageID, visible)
			VALUES		(?, ?, ?)";
		$insertStatement = WCF::getDB()->prepareStatement($sql);
		foreach ($this->visibilityExceptions as $boxIdentifier => $pages) {
			// delete old visibility exceptions
			$deleteStatement->execute([$boxes[$boxIdentifier]->boxID]);
			
			// get page ids
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('identifier IN (?)', [$pages]);
			$sql = "SELECT  pageID
				FROM    wcf".WCF_N."_page
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			$pageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
			
			// save page ids
			foreach ($pageIDs as $pageID) {
				$insertStatement->execute([$boxes[$boxIdentifier]->boxID, $pageID, ($boxes[$boxIdentifier]->visibleEverywhere ? 0 : 1)]);
			}
		}
	}
}
