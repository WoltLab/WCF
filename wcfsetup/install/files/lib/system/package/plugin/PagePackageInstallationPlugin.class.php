<?php
namespace wcf\system\package\plugin;
use wcf\data\page\PageEditor;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes CMS pages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category	Community Framework
 */
class PagePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = PageEditor::class;
	
	/**
	 * @var array
	 */
	protected $content = [];
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::$tagName
	 */
	public $tagName = 'page';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM     wcf".WCF_N."_page
			WHERE           name = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$this->installation->getPackageID()
			]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::getElement()
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
		else if ($element->tagName === 'displayname') {
			// <displayname> can occur multiple times using the `language` attribute
			if (!isset($elements['displayName'])) $elements['displayName'] = [];
			
			$elements['displayName'][$element->getAttribute('language')] = $element->nodeValue;
		}
		else {
			$elements[$element->tagName] = $nodeValue;
		}
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::prepareImport()
	 * @throws SystemException
	 */
	protected function prepareImport(array $data) {
		$isStatic = false;
		if (!empty($data['elements']['content'])) {
			$isStatic = true;
			
			$content = [];
			foreach ($data['elements']['content'] as $language => $contentData) {
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
		if (isset($data['elements']['displayName'][$defaultLanguageCode])) {
			// use the default language
			$displayName = $data['elements']['displayName'][$defaultLanguageCode];
		}
		else if (isset($data['elements']['displayName']['en'])) {
			// use the value for English
			$displayName = $data['elements']['displayName']['en'];
		}
		else {
			// fallback to the display name without/empty language attribute
			$displayName = $data['elements']['displayName'][''];
		}
		
		$parentPageID = null;
		if (!empty($data['elements']['parent'])) {
			$sql = "SELECT  pageID
				FROM    wcf".WCF_N."_".$this->tableName."
				WHERE   name = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([$data['elements']['parent']]);
			$row = $statement->fetchSingleRow();
			if ($row === false) {
				throw new SystemException("Unknown parent page '" . $data['elements']['parent'] . "' for page identifier '" . $data['attributes']['name'] . "'");
			}
			
			$parentPageID = $row['pageID'];
		}
		
		return [
			'content' => ($isStatic) ? $data['elements']['content'] : [],
			'controller' => ($isStatic) ? '' : $data['elements']['controller'],
			'controllerCustomURL' => ($isStatic || empty($data['elements']['customurl'])) ? '' : $data['elements']['customurl'],
			'displayName' => $displayName,
			'name' => $data['attributes']['name'],
			'parentPageID' => $parentPageID
		];
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	name = ?
				AND packageID = ?";
		$parameters = array(
			$data['name'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::import()
	 */
	protected function import(array $row, array $data) {
		// extract content
		$content = $data['content'];
		unset($data['content']);
		
		if ($row !== false) {
			// allow only updating of controller, everything else would overwrite user modifications
			if (!empty($data['controller'])) {
				$object = parent::import($row, ['controller' => $data['controller']]);
			}
			else {
				$baseClass = call_user_func(array($this->className, 'getBaseClass'));
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
	 * @see	AbstractXMLPackageInstallationPlugin::postImport()
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
