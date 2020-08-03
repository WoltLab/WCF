<?php
namespace wcf\system\package\plugin;
use wcf\data\language\Language;
use wcf\data\package\PackageCache;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\data\page\PageEditor;
use wcf\data\page\PageList;
use wcf\data\page\PageNode;
use wcf\data\page\PageNodeTree;
use wcf\page\IPage;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\option\OptionFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\user\group\option\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\LanguageFactory;
use wcf\system\page\handler\IMenuPageHandler;
use wcf\system\request\RouteHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes CMS pages.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 * @since	3.0
 */
class PagePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	use TXmlGuiPackageInstallationPlugin;
	
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
				
				// @deprecated
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
				$allowSpidersToIndex = $row['allowSpidersToIndex'] ?? 0;
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
					'handler' => $data['handler'] ?? '',
					'options' => $data['options'] ?? '',
					'permissions' => $data['permissions'] ?? '',
					'excludeFromLandingPage' => $data['excludeFromLandingPage'] ?? 0,
					'allowSpidersToIndex' => $allowSpidersToIndex,
					'requireObjectID' => $data['requireObjectID'],
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
	 * @since	3.1
	 */
	public static function getSyncDependencies() {
		return ['language'];
	}
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	protected function addFormFields(IFormDocument $form) {
		$tabContainter = TabMenuFormContainer::create('tabMenu');
		$form->appendChild($tabContainter);
		
		$dataTab = TabFormContainer::create('dataTab')
			->label('wcf.global.form.data');
		$tabContainter->appendChild($dataTab);
		$dataContainer = FormContainer::create('dataTabData');
		$dataTab->appendChild($dataContainer);
		
		$contentTab = TabFormContainer::create('contentTab')
			->label('wcf.acp.pip.page.content');
		$tabContainter->appendChild($contentTab);
		$contentContainer = FormContainer::create('contentTabContent');
		$contentTab->appendChild($contentContainer);
		
		$dataContainer->appendChildren([
			TextFormField::create('identifier')
				->label('wcf.acp.pip.page.identifier')
				->description('wcf.acp.pip.page.identifier.description')
				->required()
				->addValidator(FormFieldValidatorUtil::getDotSeparatedStringValidator(
					'wcf.acp.pip.page.identifier',
					4
				))
				->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
					if (
						$formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE ||
						$this->editedEntry->getAttribute('identifier') !== $formField->getValue()
					) {
						$pageList = new PageList();
						$pageList->getConditionBuilder()->add('identifier = ?', [$formField->getValue()]);
						
						if ($pageList->countObjects() > 0) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'notUnique',
									'wcf.acp.pip.page.identifier.error.notUnique'
								)
							);
						}
					}
				})),
			
			RadioButtonFormField::create('pageType')
				->label('wcf.acp.pip.page.pageType')
				->description('wcf.acp.pip.page.pageType.description')
				->options(array_combine(Page::$availablePageTypes, Page::$availablePageTypes))
				->addClass('floated'),
			
			TextFormField::create('name')
				->label('wcf.acp.pip.page.name')
				->description('wcf.acp.pip.page.name.description')
				->required()
				->i18n()
				->i18nRequired()
				->languageItemPattern('__NONE__'),
			
			ClassNameFormField::create('controller')
				->label('wcf.acp.pip.page.controller')
				->implementedInterface(IPage::class)
				->required(),
			
			ClassNameFormField::create('handler')
				->label('wcf.acp.pip.page.handler')
				->implementedInterface(IMenuPageHandler::class),
			
			BooleanFormField::create('requireObjectID')
				->label('wcf.acp.pip.page.requireObjectID')
				->description('wcf.acp.pip.page.requireObjectID.description'),
			
			SingleSelectionFormField::create('parent')
				->label('wcf.acp.pip.page.parent')
				->required()
				->filterable()
				->options(function() {
					$pageNodeList = (new PageNodeTree())->getNodeList();
					
					$nestedOptions = [[
						'depth' => 0,
						'label' => 'wcf.global.noSelection',
						'value' => ''
					]];
					
					$packageIDs = array_merge(
						[$this->installation->getPackage()->packageID],
						array_keys($this->installation->getPackage()->getAllRequiredPackages())
					);
					
					/** @var PageNode $pageNode */
					foreach ($pageNodeList as $pageNode) {
						if (in_array($pageNode->packageID, $packageIDs)) {
							$nestedOptions[] = [
								'depth' => $pageNode->getDepth() - 1,
								'label' => $pageNode->name,
								'value' => $pageNode->identifier
							];
						}
					}
					
					return $nestedOptions;
				}, true)
				->addValidator(new FormFieldValidator('selfParent', function(SingleSelectionFormField $formField) {
					/** @var TextFormField $identifier */
					$identifier = $formField->getDocument()->getNodeById('identifier');
					
					if ($identifier->getSaveValue() === $formField->getValue()) {
						$formField->addValidationError(
							new FormFieldValidationError(
								'selfParent',
								'wcf.acp.pip.page.parent.error.selfParent'
							)
						);
					}
				})),
			
			BooleanFormField::create('hasFixedParent')
				->label('wcf.acp.pip.page.hasFixedParent')
				->description('wcf.acp.pip.page.hasFixedParent.description'),
			
			OptionFormField::create()
				->description('wcf.acp.pip.page.options.description')
				->packageIDs(array_merge(
					[$this->installation->getPackage()->packageID],
					array_keys($this->installation->getPackage()->getAllRequiredPackages())
				)),
			
			UserGroupOptionFormField::create()
				->description('wcf.acp.pip.page.permissions.description')
				->packageIDs(array_merge(
					[$this->installation->getPackage()->packageID],
					array_keys($this->installation->getPackage()->getAllRequiredPackages())
				)),
			
			ItemListFormField::create('cssClassName')
				->label('wcf.acp.pip.page.cssClassName')
				->description('wcf.acp.pip.page.cssClassName.description'),
			
			BooleanFormField::create('allowSpidersToIndex')
				->label('wcf.acp.pip.page.allowSpidersToIndex'),
			
			BooleanFormField::create('excludeFromLandingPage')
				->label('wcf.acp.pip.page.excludeFromLandingPage'),
			
			BooleanFormField::create('availableDuringOfflineMode')
				->label('wcf.acp.pip.page.availableDuringOfflineMode')
		]);
		
		$contentContainer->appendChildren([
			TitleFormField::create('contentTitle')
				->objectProperty('title')
				->label('wcf.acp.pip.page.contentTitle')
				->i18n()
				->i18nRequired()
				->languageItemPattern('__NONE__'),
			
			MultilineTextFormField::create('contentContent')
				->objectProperty('content')
				->label('wcf.acp.pip.page.contentContent')
				->i18n()
				->i18nRequired()
				->languageItemPattern('__NONE__'),
			
			TextFormField::create('contentCustomURL')
				->objectProperty('customURL')
				->label('wcf.acp.pip.page.contentCustomURL')
				->i18n()
				->i18nRequired()
				->languageItemPattern('__NONE__'),
			
			TextFormField::create('contentMetaDescription')
				->objectProperty('metaDescription')
				->label('wcf.acp.pip.page.contentMetaDescription')
				->i18n()
				->i18nRequired()
				->languageItemPattern('__NONE__'),
			
			TextFormField::create('contentMetaKeywords')
				->objectProperty('metaKeywords')
				->label('wcf.acp.pip.page.contentMetaKeywords')
				->i18n()
				->i18nRequired()
				->languageItemPattern('__NONE__'),
		]);
		
		// dependencies
		
		/** @var RadioButtonFormField $pageType */
		$pageType = $form->getNodeById('pageType');
		foreach (['controller', 'handler', 'requireObjectID'] as $systemElement) {
			$form->getNodeById($systemElement)->addDependency(
				ValueFormFieldDependency::create('pageType')
					->field($pageType)
					->values(['system'])
			);
		}
		
		foreach (['contentContent', 'contentCustomURL', 'contentMetaDescription', 'contentMetaKeywords'] as $nonSystemElement) {
			$form->getNodeById($nonSystemElement)->addDependency(
				ValueFormFieldDependency::create('pageType')
					->field($pageType)
					->values(['system'])
					->negate()
			);
		}
	}
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	protected function fetchElementData(\DOMElement $element, $saveData) {
		$data = [
			'identifier' => $element->getAttribute('identifier'),
			'originIsSystem' => 1,
			'packageID' => $this->installation->getPackageID(),
			'pageType' => $element->getElementsByTagName('pageType')->item(0)->nodeValue,
			'name' => [],
			'title' => [],
			'content' => [],
			'customURL' => [],
			'metaDescription' => [],
			'metaKeywords' => []
		];
		
		/** @var \DOMElement $name */
		foreach ($element->getElementsByTagName('name') as $name) {
			$data['name'][LanguageFactory::getInstance()->getLanguageByCode($name->getAttribute('language'))->languageID] = $name->nodeValue;
		}
		
		$optionalElements = [
			'controller', 'handler', 'hasFixedParent',
			'parent', 'options', 'permissions', 'cssClassName', 'allowSpidersToIndex',
			'excludeFromLandingPage', 'availableDuringOfflineMode', 'requireObjectID'
		];
		
		$zeroDefaultOptions = [
			'hasFixedParent',
			'allowSpidersToIndex',
			'excludeFromLandingPage',
			'availableDuringOfflineMode',
			'requireObjectID'
		];
		
		foreach ($optionalElements as $optionalElementName) {
			$optionalElement = $element->getElementsByTagName($optionalElementName)->item(0);
			if ($optionalElement !== null) {
				$data[$optionalElementName] = $optionalElement->nodeValue;
			}
			else if ($saveData) {
				if (in_array($optionalElementName, $zeroDefaultOptions)) {
					$data[$optionalElementName] = 0;
				}
				else {
					$data[$optionalElementName] = '';
				}
			}
		}
		
		$readData = function($languageID, \DOMElement $content) use (&$data, $saveData) {
			foreach (['title', 'content', 'customURL', 'metaDescription', 'metaKeywords'] as $contentElementName) {
				$contentElement = $content->getElementsByTagName($contentElementName)->item(0);
				if (!isset($data[$contentElementName])) {
					$data[$contentElementName] = [];
				}
				
				if ($contentElement) {
					$data[$contentElementName][$languageID] = $contentElement->nodeValue;
				}
				else if ($saveData) {
					$data[$contentElementName][$languageID] = '';
				}
			}
		};
		
		/** @var \DOMElement $content */
		foreach ($element->getElementsByTagName('content') as $content) {
			$languageCode = $content->getAttribute('language');
			if ($languageCode === '') {
				foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
					$readData($language->languageID, $content);
				}
			}
			else {
				$readData(
					LanguageFactory::getInstance()->getLanguageByCode($languageCode)->languageID,
					$content
				);
			}
		}
		
		if ($saveData) {
			if ($this->editedEntry !== null) {
				unset($data['name']);
			}
			else {
				$titles = [];
				foreach ($data['name'] as $languageID => $title) {
					$titles[LanguageFactory::getInstance()->getLanguage($languageID)->languageCode] = $title;
				}
				
				if (isset($data['name'][LanguageFactory::getInstance()->getDefaultLanguage()->languageID])) {
					// use the default language
					$data['name'] = $data['name'][LanguageFactory::getInstance()->getDefaultLanguage()->languageID];
				}
				else {
					$english = LanguageFactory::getInstance()->getLanguageByCode('en');
					if ($english !== null && isset($data['name'][$english->languageID])) {
						$data['name'] = $data['name'][$english->languageID];
					}
					else {
						$data['name'] = reset($data['name']);
					}
				}
			}
			
			$content = [];
			
			foreach (['title', 'content', 'customURL', 'metaDescription', 'metaKeywords'] as $contentProperty) {
				if (!empty($data[$contentProperty])) {
					foreach ($data[$contentProperty] as $languageID => $value) {
						$languageCode = LanguageFactory::getInstance()->getLanguage($languageID)->languageCode;
						
						if (!isset($content[$languageCode])) {
							$content[$languageCode] = [];
						}
						
						$content[$languageCode][$contentProperty] = $value;
					}
				}
				
				unset($data[$contentProperty]);
			}
			
			foreach ($content as $languageCode => $values) {
				foreach (['title', 'content', 'customURL', 'metaDescription', 'metaKeywords'] as $contentProperty) {
					if (!isset($values[$contentProperty])) {
						$content[$languageCode][$contentProperty] = '';
					}
				}
			}
			
			$data['content'] = $content;
			
			if (isset($data['parent'])) {
				$parent = $data['parent'];
				unset($data['parent']);
				
				if (!empty($parent)) {
					$data['parentPageID'] = Page::getPageByIdentifier($parent)->pageID;
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	public function getElementIdentifier(\DOMElement $element) {
		return $element->getAttribute('identifier');
	}
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	protected function setEntryListKeys(IDevtoolsPipEntryList $entryList) {
		$entryList->setKeys([
			'identifier' => 'wcf.acp.pip.page.identifier',
			'pageType' => 'wcf.acp.pip.page.pageType'
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form) {
		$formData = $form->getData();
		$data = $formData['data'];
		
		$page = $document->createElement($this->tagName);
		$page->setAttribute('identifier', $data['identifier']);
		
		$page->appendChild($document->createElement('pageType', $data['pageType']));
		
		$this->appendElementChildren(
			$page,
			['controller' => '',],
			$form
		);
		
		foreach ($formData['name_i18n'] as $languageID => $name) {
			$name = $document->createElement('name', $this->getAutoCdataValue($name));
			$name->setAttribute('language', LanguageFactory::getInstance()->getLanguage($languageID)->languageCode);
			
			$page->appendChild($name);
		}
		
		$this->appendElementChildren(
			$page,
			[
				'handler' => '',
				'hasFixedParent' => 0,
				'parent' => '',
				'options' => '',
				'permissions' => '',
				'cssClassName' => '',
				'allowSpidersToIndex' => 0,
				'excludeFromLandingPage' => 0,
				'availableDuringOfflineMode' => 0,
				'requireObjectID' => 0
			],
			$form
		);
		
		$languages = LanguageFactory::getInstance()->getLanguages();
		
		// sort languages by language code but keep English first
		uasort($languages, function(Language $language1, Language $language2) {
			if ($language1->languageCode === 'en') {
				return -1;
			}
			else if ($language2->languageCode === 'en') {
				return 1;
			}
			
			return $language1->languageCode <=> $language2->languageCode;
		});
		
		foreach ($languages as $language) {
			$content = null;
			
			foreach (['title', 'content', 'customURL', 'metaDescription', 'metaKeywords'] as $property) {
				if (!empty($formData[$property . '_i18n'][$language->languageID])) {
					if ($content === null) {
						$content = $document->createElement('content');
						$content->setAttribute('language', $language->languageCode);
						
						$page->appendChild($content);
					}
					
					if ($property === 'content') {
						$contentContent = $document->createElement('content');
						$contentContent->appendChild(
							$document->createCDATASection(
								StringUtil::escapeCDATA(StringUtil::unifyNewlines(
									$formData[$property . '_i18n'][$language->languageID]
								))
							)
						);
						
						$content->appendChild($contentContent);
					}
					else {
						$content->appendChild(
							$document->createElement(
								$property,
								$formData[$property . '_i18n'][$language->languageID]
							)
						);
					}
				}
			}
		}
		
		return $page;
	}
}
