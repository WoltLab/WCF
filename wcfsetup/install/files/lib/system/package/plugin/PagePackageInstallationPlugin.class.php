<?php
namespace wcf\system\package\plugin;
use wcf\data\package\PackageCache;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\data\page\PageEditor;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\RouteHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes CMS pages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 * @since	3.0
 */
class PagePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IIdempotentPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = PageEditor::class;
	
	/**
	 * page content
	 * @var mixed[]
	 */
	protected $content = [];
	
	/**
	 * pages objects
	 * @var Page[]
	 */
	protected $pages = [];
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'page';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$pages = [];
		foreach ($items as $item) {
			$page = Page::getPageByIdentifier($item['attributes']['identifier']);
			if ($page !== null && $page->pageID && $page->packageID == $this->installation->getPackageID()) $pages[] = $page;
		}
		
		if (!empty($pages)) {
			$pageAction = new PageAction($pages, 'delete');
			$pageAction->executeAction();
		}
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
	 * @throws	SystemException
	 */
	protected function prepareImport(array $data) {
		$pageType = $data['elements']['pageType'];
		
		if (!empty($data['elements']['content'])) {
			$content = [];
			foreach ($data['elements']['content'] as $language => $contentData) {
				if ($pageType != 'system' && !RouteHandler::isValidCustomUrl($contentData['customURL'])) {
					throw new SystemException("Invalid custom url for page content '" . $language . "', page identifier '" . $data['attributes']['identifier'] . "'");
				}
				
				$content[$language] = [
					'content' => (!empty($contentData['content'])) ? StringUtil::trim($contentData['content']) : '',
					'customURL' => (!empty($contentData['customURL'])) ? StringUtil::trim($contentData['customURL']) : '',
					'metaDescription' => (!empty($contentData['metaDescription'])) ? StringUtil::trim($contentData['metaDescription']) : '',
					'metaKeywords' => (!empty($contentData['metaKeywords'])) ? StringUtil::trim($contentData['metaKeywords']) : '',
					'title' => (!empty($contentData['title'])) ? StringUtil::trim($contentData['title']) : ''
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
		else if (isset($data['elements']['name'][''])) {
			// fallback to the display name without/empty language attribute
			$name = $data['elements']['name'][''];
		}
		else {
			// use whichever value is present, regardless of the language
			$name = reset($data['elements']['name']);
		}
		
		$parentPageID = null;
		if (!empty($data['elements']['parent'])) {
			$sql = "SELECT	pageID
				FROM	wcf".WCF_N."_".$this->tableName."
				WHERE	identifier = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([$data['elements']['parent']]);
			$row = $statement->fetchSingleRow();
			if ($row === false) {
				throw new SystemException("Unknown parent page '" . $data['elements']['parent'] . "' for page identifier '" . $data['attributes']['identifier'] . "'");
			}
			
			$parentPageID = $row['pageID'];
		}
		
		// validate page type
		$controller = '';
		$handler = '';
		$controllerCustomURL = '';
		$identifier = $data['attributes']['identifier'];
		$isMultilingual = 0;
		switch ($pageType) {
			case 'system':
				if (empty($data['elements']['controller'])) {
					throw new SystemException("Missing required element 'controller' for 'system'-type page '{$identifier}'");
				}
				$controller = $data['elements']['controller'];
				
				if (!empty($data['elements']['handler'])) {
					$handler = $data['elements']['handler'];
				}
				
				if (!empty($data['elements']['controllerCustomURL'])) {
					$controllerCustomURL = $data['elements']['controllerCustomURL'];
					if ($controllerCustomURL && !RouteHandler::isValidCustomUrl($controllerCustomURL)) {
						throw new SystemException("Invalid custom url for page identifier '" . $data['attributes']['identifier'] . "'");
					}
				}
				
				break;
			
			case 'html':
			case 'text':
			case 'tpl':
				if (empty($data['elements']['content'])) {
					throw new SystemException("Missing required 'content' element(s) for page '{$identifier}'");
				}
				
				if (count($data['elements']['content']) === 1) {
					if (!isset($data['elements']['content'][''])) {
						throw new SystemException("Expected one 'content' element without a 'language' attribute for page '{$identifier}'");
					}
				}
				else {
					$isMultilingual = 1;
					if (isset($data['elements']['content'][''])) {
						throw new SystemException("Cannot mix 'content' elements with and without 'language' attribute for page '{$identifier}'");
					}
				}
				
				break;
			
			default:
				throw new SystemException("Unknown type '{$pageType}' for page '{$identifier}");
				break;
		}
		
		// get application package id
		$applicationPackageID = 1;
		if ($this->installation->getPackage()->isApplication) {
			$applicationPackageID = $this->installation->getPackageID();
		}
		if (!empty($data['elements']['application'])) {
			$application = PackageCache::getInstance()->getPackageByIdentifier($data['elements']['application']);
			if ($application === null || !$application->isApplication) {
				throw new SystemException("Unknown application '".$data['elements']['application']."' for page '{$identifier}");
			}
			$applicationPackageID = $application->packageID;
		}
		
		return [
			'pageType' => $pageType,
			'content' => (!empty($data['elements']['content'])) ? $data['elements']['content'] : [],
			'controller' => $controller,
			'handler' => $handler,
			'controllerCustomURL' => $controllerCustomURL,
			'identifier' => $identifier,
			'isMultilingual' => $isMultilingual,
			'lastUpdateTime' => TIME_NOW,
			'name' => $name,
			'originIsSystem' => 1,
			'parentPageID' => $parentPageID,
			'applicationPackageID' => $applicationPackageID,
			'requireObjectID' => (!empty($data['elements']['requireObjectID'])) ? 1 : 0,
			'options' => isset($data['elements']['options']) ? $data['elements']['options'] : '',
			'permissions' => isset($data['elements']['permissions']) ? $data['elements']['permissions'] : '',
			'hasFixedParent' => ($pageType == 'system' && !empty($data['elements']['hasFixedParent'])) ? 1 : 0,
			'cssClassName' => isset($data['elements']['cssClassName']) ? $data['elements']['cssClassName'] : '',
			'availableDuringOfflineMode' => (!empty($data['elements']['availableDuringOfflineMode'])) ? 1 : 0,
			'allowSpidersToIndex' => (!empty($data['elements']['allowSpidersToIndex'])) ? 1 : 0,
			'excludeFromLandingPage' => (!empty($data['elements']['excludeFromLandingPage'])) ? 1 : 0
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
		
		/** @var Page $page */
		if (!empty($row)) {
			// allow update of `controller`, `handler` and `excludeFromLandingPage`
			// only, prevents user modifications form being overwritten
			if (!empty($data['controller'])) {
				$allowSpidersToIndex = $row['allowSpidersToIndex'];
				if ($allowSpidersToIndex == 2) {
					// The value `2` resolves to be true-ish, eventually resulting in the same behavior
					// when setting it to `1`. This value is special to the 3.0 -> 3.1 upgrade, because
					// it force-enables the visibility, while also being some sort of indicator for non-
					// user-modified values. The page edit form will set it to either `1` or `0`, there-
					// fore `2` means that we can safely update the value w/o breaking the user's choice. 
					$allowSpidersToIndex = $data['allowSpidersToIndex'];
				}
				
				$page = parent::import($row, [
					'controller' => $data['controller'],
					'handler' => $data['handler'],
					'options' => $data['options'],
					'permissions' => $data['permissions'],
					'excludeFromLandingPage' => $data['excludeFromLandingPage'],
					'allowSpidersToIndex' => $allowSpidersToIndex
				]);
			}
			else {
				$baseClass = call_user_func([$this->className, 'getBaseClass']);
				$page = new $baseClass(null, $row);
			}
		}
		else {
			// import
			$page = parent::import($row, $data);
		}
		
		// store content for later import
		$this->pages[$page->pageID] = $page;
		$this->content[$page->pageID] = $content;
		
		return $page;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function postImport() {
		if (!empty($this->content)) {
			$sql = "SELECT  COUNT(*) AS count
				FROM    wcf".WCF_N."_page_content
				WHERE   pageID = ?
					AND languageID IS NULL";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_page_content
							(pageID, languageID, title, content, metaDescription, metaKeywords, customURL)
				VALUES			(?, ?, ?, ?, ?, ?, ?)";
			$insertStatement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($this->content as $pageID => $contentData) {
				foreach ($contentData as $languageCode => $content) {
					$languageID = null;
					if ($languageCode != '') {
						$language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
						if ($language === null) continue;
						
						$languageID = $language->languageID;
					}
					
					if ($languageID === null) {
						$statement->execute([$pageID]);
						if ($statement->fetchColumn()) continue;
					}
					
					$insertStatement->execute([
						$pageID,
						$languageID,
						$content['title'],
						$content['content'],
						$content['metaDescription'],
						$content['metaKeywords'],
						$content['customURL']
					]);
					
					// generate template if page's type is 'tpl'
					$page = new Page($pageID);
					if ($page->pageType == 'tpl') {
						(new PageEditor($page))->updateTemplate($languageID, $content['content']);
					}
				}
			}
			WCF::getDB()->commitTransaction();
			
			// create search index tables
			SearchIndexManager::getInstance()->createSearchIndices();
			
			// update search index
			foreach ($this->pages as $pageID => $page) {
				if ($page->pageType == 'text' || $page->pageType == 'html') {
					foreach ($page->getPageContents() as $languageID => $pageContent) {
						SearchIndexManager::getInstance()->set(
							'com.woltlab.wcf.page',
							$pageContent->pageContentID,
							$pageContent->content,
							$pageContent->title,
							0,
							null,
							'',
							$languageID ?: null
						);
					}
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getSyncDependencies() {
		return ['language'];
	}
}
