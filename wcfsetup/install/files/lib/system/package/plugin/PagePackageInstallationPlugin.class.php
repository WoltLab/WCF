<?php
namespace wcf\system\package\plugin;
use wcf\data\page\PageEditor;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes CMS pages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category	Community Framework
 * @since	2.2
 */
class PagePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = PageEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $content = [];
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'page';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM     wcf".WCF_N."_page
			WHERE           identifier = ?
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
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		// read content
		if ($element->tagName === 'content') {
			if (!isset($elements['content'])) $elements['content'] = [];
			
			$children = [];
			/** @var \DOMElement $child */
			foreach ($xpath->query('child::*', $element) as $child) {
				$children[$child->tagName] = $child->nodeValue;
			}
			
			$elements[$element->tagName][$element->getAttribute('language')] = $children;
		}
		else if ($element->tagName === 'name') {
			// <name> can occur multiple times using the `language` attribute
			if (!isset($elements['name'])) $elements['name'] = [];
			
			$elements['name'][$element->getAttribute('language')] = $element->nodeValue;
		}
		else {
			$elements[$element->tagName] = $nodeValue;
		}
	}
	
	/**
	 * @inheritDoc
	 * @throws      SystemException
	 */
	protected function prepareImport(array $data) {
		$isStatic = false;
		if (!empty($data['elements']['content'])) {
			$isStatic = true;
			
			$content = [];
			foreach ($data['elements']['content'] as $language => $contentData) {
				if (!RouteHandler::isValidCustomUrl($contentData['customurl'])) {
					throw new SystemException("Invalid custom url for page content '" . $language . "', page identifier '" . $data['attributes']['identifier'] . "'");
				}
				
				$content[$language] = [
					'content' => $contentData['content'],
					'customURL' => $contentData['customurl'],
					'metaDescription' => (!empty($contentData['metadescription'])) ? StringUtil::trim($contentData['metadescription']) : '',
					'metaKeywords' => (!empty($contentData['metakeywords'])) ? StringUtil::trim($contentData['metakeywords']) : '',
					'title' => $contentData['title']
				];
			}
			
			$data['elements']['content'] = $content;
		}
		
		// pick the display name by choosing the default language, or 'en' or '' (empty string)
		$defaultLanguageCode = LanguageFactory::getInstance()->getDefaultLanguage()->getFixedLanguageCode();
		if (isset($data['elements']['name'][$defaultLanguageCode])) {
			// use the default language
			$name = $data['elements']['name'][$defaultLanguageCode];
		}
		else if (isset($data['elements']['name']['en'])) {
			// use the value for English
			$name = $data['elements']['name']['en'];
		}
		else {
			// fallback to the display name without/empty language attribute
			$name = $data['elements']['name'][''];
		}
		
		$parentPageID = null;
		if (!empty($data['elements']['parent'])) {
			$sql = "SELECT  pageID
				FROM    wcf".WCF_N."_".$this->tableName."
				WHERE   identifier = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([$data['elements']['parent']]);
			$row = $statement->fetchSingleRow();
			if ($row === false) {
				throw new SystemException("Unknown parent page '" . $data['elements']['parent'] . "' for page identifier '" . $data['attributes']['identifier'] . "'");
			}
			
			$parentPageID = $row['pageID'];
		}
		
		$customUrl = ($isStatic || empty($data['elements']['customurl'])) ? '' : $data['elements']['customurl'];
		if ($customUrl && !RouteHandler::isValidCustomUrl($customUrl)) {
			throw new SystemException("Invalid custom url for page identifier '" . $data['attributes']['identifier'] . "'");
		}
		
		return [
			'content' => ($isStatic) ? $data['elements']['content'] : [],
			'controller' => ($isStatic) ? '' : $data['elements']['controller'],
			'handler' => (!$isStatic && !empty($data['elements']['handler'])) ? $data['elements']['handler'] : '',
			'controllerCustomURL' => $customUrl,
			'identifier' => $data['attributes']['identifier'],
			'isMultilingual' => ($isStatic) ? 1 : 0,
			'lastUpdateTime' => TIME_NOW,
			'name' => $name,
			'originIsSystem' => 1,
			'parentPageID' => $parentPageID
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
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
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		// extract content
		$content = $data['content'];
		unset($data['content']);
		
		if (!empty($row)) {
			// allow only updating of controller, everything else would overwrite user modifications
			if (!empty($data['controller'])) {
				$object = parent::import($row, ['controller' => $data['controller']]);
			}
			else {
				$baseClass = call_user_func([$this->className, 'getBaseClass']);
				$object = new $baseClass(null, $row);
			}
		}
		else {
			// import
			$object = parent::import($row, $data);
		}
		
		// store content for later import
		$this->content[$object->pageID] = $content;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function postImport() {
		if (!empty($this->content)) {
			$sql = "INSERT IGNORE INTO      wcf".WCF_N."_page_content
							(pageID, languageID, title, content, metaDescription, metaKeywords, customURL)
				VALUES                  (?, ?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($this->content as $pageID => $contentData) {
				foreach ($contentData as $languageCode => $content) {
					$language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
					if ($language !== null) {
						$statement->execute([
							$pageID,
							$language->languageID,
							$content['title'],
							$content['content'],
							$content['metaDescription'],
							$content['metaKeywords'],
							$content['customURL']
						]);
					}
				}
			}
			WCF::getDB()->commitTransaction();
		}
	}
}
