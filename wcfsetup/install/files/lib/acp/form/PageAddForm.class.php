<?php
namespace wcf\acp\form;
use wcf\data\application\Application;
use wcf\data\application\ApplicationList;
use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\data\language\Language;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\data\page\PageEditor;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the page add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.0
 */
class PageAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.page.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManagePage'];
	
	/**
	 * true if created page is multi-lingual
	 * @var	boolean
	 */
	public $isMultilingual = 0;
	
	/**
	 * page type
	 * @var	string
	 */
	public $pageType = '';
	
	/**
	 * parent page id
	 * @var	integer
	 */
	public $parentPageID = 0;
	
	/**
	 * page name
	 * @var	string
	 */
	public $name = '';
	
	/**
	 * true if page is disabled
	 * @var	boolean
	 */
	public $isDisabled = 0;
	
	/**
	 * true if page is landing page
	 * @var	boolean
	 */
	public $isLandingPage = 0;
	
	/**
	 * application id of the page
	 * @var	integer
	 */
	public $applicationPackageID = 1;
	
	/**
	 * list of available applications
	 * @var	Application[]
	 */
	public $availableApplications = [];
	
	/**
	 * list of available boxes
	 * @var	Box[]
	 */
	public $availableBoxes = [];
	
	/**
	 * list of available languages
	 * @var	Language[]
	 */
	public $availableLanguages = [];
	
	/**
	 * page custom URL
	 * @var	string[]
	 */
	public $customURL = [];
	
	/**
	 * page titles
	 * @var	string[]
	 */
	public $title = [];
	
	/**
	 * page contents
	 * @var	string[]
	 */
	public $content = [];
	
	/**
	 * page meta descriptions
	 * @var	string[]
	 */
	public $metaDescription = [];
	
	/**
	 * page meta keywords
	 * @var	string[]
	 */
	public $metaKeywords = [];
	
	/**
	 * list of box ids
	 * @var integer[]
	 */
	public $boxIDs = [];
	
	/**
	 * acl values
	 * @var array
	 */
	public $aclValues = [];
	
	/**
	 * @var HtmlInputProcessor[]
	 */
	public $htmlInputProcessors = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->readPageType();
		
		// get available applications
		$applicationList = new ApplicationList();
		$applicationList->readObjects();
		$this->availableApplications = $applicationList->getObjects();
		
		// get available languages
		$this->availableLanguages = LanguageFactory::getInstance()->getLanguages();
		
		// get available boxes
		$boxList = new BoxList();
		$boxList->sqlOrderBy = 'box.name';
		$boxList->readObjects();
		$this->availableBoxes = $boxList->getObjects();
	}
	
	/**
	 * Reads basic page parameters controlling type and i18n.
	 * 
	 * @throws	IllegalLinkException
	 */
	protected function readPageType() {
		if (!empty($_REQUEST['isMultilingual'])) $this->isMultilingual = 1;
		if (!empty($_REQUEST['pageType'])) $this->pageType = $_REQUEST['pageType'];
		
		// work-around to force adding pages via dialog overlay
		if (empty($_POST) && $this->pageType == '') {
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('PageList', ['showPageAddDialog' => 1]));
			exit;
		}
		
		try {
			$this->validatePageType();
		}
		catch (UserInputException $e) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['parentPageID'])) $this->parentPageID = intval($_POST['parentPageID']);
		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
		if (isset($_POST['isLandingPage'])) $this->isLandingPage = 1;
		if (isset($_POST['applicationPackageID'])) $this->applicationPackageID = intval($_POST['applicationPackageID']);
		
		if (isset($_POST['customURL']) && is_array($_POST['customURL'])) $this->customURL = ArrayUtil::trim($_POST['customURL']);
		if (isset($_POST['title']) && is_array($_POST['title'])) $this->title = ArrayUtil::trim($_POST['title']);
		if (isset($_POST['content']) && is_array($_POST['content'])) $this->content = ArrayUtil::trim($_POST['content']);
		if (isset($_POST['metaDescription']) && is_array($_POST['metaDescription'])) $this->metaDescription = ArrayUtil::trim($_POST['metaDescription']);
		if (isset($_POST['metaKeywords']) && is_array($_POST['metaKeywords'])) $this->metaKeywords = ArrayUtil::trim($_POST['metaKeywords']);
		if (isset($_POST['boxIDs']) && is_array($_POST['boxIDs'])) $this->boxIDs = ArrayUtil::toIntegerArray($_POST['boxIDs']);
		$box = Box::getBoxByIdentifier('com.woltlab.wcf.MainMenu');
		if (!in_array($box->boxID, $this->boxIDs)) $this->boxIDs[] = $box->boxID;
		
		if (isset($_POST['aclValues']) && is_array($_POST['aclValues'])) $this->aclValues = $_POST['aclValues'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		$this->validateName();
		
		$this->validatePageType();
		
		$this->validateParentPageID();
		
		$this->validateApplicationPackageID();
		
		$this->validateCustomUrls();
		
		$this->validateBoxIDs();
		
		if ($this->pageType == 'text') {
			if ($this->isMultilingual) {
				foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
					$this->htmlInputProcessors[$language->languageID] = new HtmlInputProcessor();
					$this->htmlInputProcessors[$language->languageID]->process((!empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : ''), 'com.woltlab.wcf.page.content');
				}
			}
			else {
				$this->htmlInputProcessors[0] = new HtmlInputProcessor();
				$this->htmlInputProcessors[0]->process((!empty($this->content[0]) ? $this->content[0] : ''), 'com.woltlab.wcf.page.content');
			}
		}
	}
	
	/**
	 * Validates page name.
	 */
	protected function validateName() {
		if (empty($this->name)) {
			throw new UserInputException('name');
		}
		if (Page::getPageByName($this->name)) {
			throw new UserInputException('name', 'notUnique');
		}
	}
	
	/**
	 * Validates page type.
	 * 
	 * @throws	UserInputException
	 */
	protected function validatePageType() {
		if (!in_array($this->pageType, Page::$availablePageTypes) || $this->pageType == 'system') {
			throw new UserInputException('pageType');
		}
		
		if ($this->pageType == 'system') {
			$this->isMultilingual = 0;
		}
	}
	
	/**
	 * Validates parent page id.
	 * 
	 * @throws	UserInputException
	 */
	protected function validateParentPageID() {
		if ($this->parentPageID) {
			$page = new Page($this->parentPageID);
			if (!$page->pageID) {
				throw new UserInputException('parentPageID', 'invalid');
			}
		}
	}
	
	/**
	 * Validates package id.
	 * 
	 * @throws	UserInputException
	 */
	protected function validateApplicationPackageID() {
		if (!isset($this->availableApplications[$this->applicationPackageID])) {
			throw new UserInputException('applicationPackageID', 'invalid');
		}
	}
	
	/**
	 * Validates custom urls.
	 * 
	 * @throws	UserInputException
	 */
	protected function validateCustomUrls() {
		if (empty($this->customURL) && $this->pageType != 'system') {
			if ($this->isMultilingual) {
				$language1 = reset($this->availableLanguages);
				throw new UserInputException('customURL_'.$language1->languageID);
			}
			else {
				throw new UserInputException('customURL_0');
			}
		} 
		
		foreach ($this->customURL as $languageID => $customURL) {
			$this->validateCustomUrl($languageID, $customURL);
		}
	}
	
	/**
	 * Validates given custom url.
	 * 
	 * @param       integer                 $languageID
	 * @param       string                  $customURL
	 *
	 * @throws	UserInputException
	 */
	protected function validateCustomUrl($languageID, $customURL) {
		if (empty($customURL)) {
			if ($this->pageType != 'system') {
				throw new UserInputException('customURL_' . $languageID, 'invalid');
			}
		}
		else if (!RouteHandler::isValidCustomUrl($customURL)) {
			throw new UserInputException('customURL_' . $languageID, 'invalid');
		}
		else {
			// check whether url is already in use
			if (!PageEditor::isUniqueCustomUrl($customURL, $this->applicationPackageID)) {
				throw new UserInputException('customURL_' . $languageID, 'notUnique');
			}
			
			foreach ($this->customURL as $languageID2 => $customURL2) {
				if ($languageID != $languageID2 && $customURL == $customURL2) {
					throw new UserInputException('customURL_' . $languageID, 'notUnique');
				}
			}
		}
	}
	
	/**
	 * Validates box ids.
	 * 
	 * @throws	UserInputException
	 */
	protected function validateBoxIDs() {
		foreach ($this->boxIDs as $boxID) {
			if (!isset($this->availableBoxes[$boxID])) {
				throw new UserInputException('boxIDs');
			}
		}
	}
	
	/**
	 * Prepares box to page assignments
	 * 
	 * @return	mixed[]
	 */
	protected function getBoxToPage() {
		$boxToPage = [];
		foreach ($this->availableBoxes as $box) {
			if ($box->visibleEverywhere) {
				if (!in_array($box->boxID, $this->boxIDs)) {
					$boxToPage[] = [
						'boxID' => $box->boxID,
						'visible' => 0
					];
				}
			}
			else {
				if (in_array($box->boxID, $this->boxIDs)) {
					$boxToPage[] = [
						'boxID' => $box->boxID,
						'visible' => 1
					];
				}
			}
		}
		
		return $boxToPage;
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// prepare page content
		$content = [];
		if ($this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'customURL' => (!empty($this->customURL[$language->languageID]) ? $this->customURL[$language->languageID] : ''),
					'title' => (!empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : ''),
					'content' => (!empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : ''),
					'htmlInputProcessor' => (isset($this->htmlInputProcessors[$language->languageID]) ? $this->htmlInputProcessors[$language->languageID] : null),
					'metaDescription' => (!empty($this->metaDescription[$language->languageID]) ? $this->metaDescription[$language->languageID] : ''),
					'metaKeywords' => (!empty($this->metaKeywords[$language->languageID]) ? $this->metaKeywords[$language->languageID] : '')
				];
			}
		}
		else {
			$content[0] = [
				'customURL' => (!empty($this->customURL[0]) ? $this->customURL[0] : ''),
				'title' => (!empty($this->title[0]) ? $this->title[0] : ''),
				'content' => (!empty($this->content[0]) ? $this->content[0] : ''),
				'htmlInputProcessor' => (isset($this->htmlInputProcessors[0]) ? $this->htmlInputProcessors[0] : null),
				'metaDescription' => (!empty($this->metaDescription[0]) ? $this->metaDescription[0] : ''),
				'metaKeywords' => (!empty($this->metaKeywords[0]) ? $this->metaKeywords[0] : '')
			];
		}
		
		$this->objectAction = new PageAction([], 'create', ['data' => array_merge($this->additionalFields, [
			'parentPageID' => ($this->parentPageID ?: null),
			'pageType' => $this->pageType,
			'name' => $this->name,
			'isDisabled' => ($this->isDisabled) ? 1 : 0,
			'isLandingPage' => 0,
			'applicationPackageID' => $this->applicationPackageID,
			'lastUpdateTime' => TIME_NOW,
			'isMultilingual' => $this->isMultilingual,
			'identifier' => '',
			'packageID' => 1
		]), 'content' => $content, 'boxToPage' => $this->getBoxToPage()]);
		
		/** @var Page $page */
		$page = $this->objectAction->executeAction()['returnValues'];
		
		// set generic page identifier
		$pageEditor = new PageEditor($page);
		$pageEditor->update([
			'identifier' => 'com.woltlab.wcf.generic'.$page->pageID
		]);
		
		if ($this->isLandingPage) {
			$page->setAsLandingPage();
		}
		
		// save acl
		SimpleAclHandler::getInstance()->setValues('com.woltlab.wcf.page', $page->pageID, $this->aclValues);
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
		
		// reset variables
		$this->parentPageID = $this->isDisabled = $this->isLandingPage = 0;
		$this->applicationPackageID = 1;
		$this->name = '';
		$this->customURL = $this->title = $this->content = $this->metaDescription = $this->metaKeywords = $this->boxIDs = $this->aclValues = [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// set default values
		if (empty($_POST)) {
			foreach ($this->availableBoxes as $box) {
				if ($box->visibleEverywhere) $this->boxIDs[] = $box->boxID;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'parentPageID' => $this->parentPageID,
			'pageType' => $this->pageType,
			'name' => $this->name,
			'isDisabled' => $this->isDisabled,
			'isLandingPage' => $this->isLandingPage,
			'isMultilingual' => $this->isMultilingual,
			'applicationPackageID' => $this->applicationPackageID,
			'customURL' => $this->customURL,
			'title' => $this->title,
			'content' => $this->content,
			'metaDescription' => $this->metaDescription,
			'metaKeywords' => $this->metaKeywords,
			'boxIDs' => $this->boxIDs,
			'availableApplications' => $this->availableApplications,
			'availableLanguages' => $this->availableLanguages,
			'availableBoxes' => $this->availableBoxes,
			'pageNodeList' => (new PageNodeTree())->getNodeList(),
			'aclValues' => SimpleAclHandler::getInstance()->getOutputValues($this->aclValues)
		]);
	}
}
