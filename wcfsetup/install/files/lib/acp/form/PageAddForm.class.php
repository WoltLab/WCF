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
		
		// get boxes
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
				if ($languageID != $languageID2 && $customURL = $customURL2) {
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
		
		$parseHTML = function($content) {
			if ($this->pageType == 'text') {
				$htmlInputProcessor = new HtmlInputProcessor();
				$content = $htmlInputProcessor->process($content);
			}
			
			return $content;
		};
		
		// prepare page content
		$content = [];
		if ($this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'customURL' => (!empty($_POST['customURL'][$language->languageID]) ? $_POST['customURL'][$language->languageID] : ''),
					'title' => (!empty($_POST['title'][$language->languageID]) ? $_POST['title'][$language->languageID] : ''),
					'content' => (!empty($_POST['content'][$language->languageID]) ? $parseHTML($_POST['content'][$language->languageID]) : ''),
					'metaDescription' => (!empty($_POST['metaDescription'][$language->languageID]) ? $_POST['metaDescription'][$language->languageID] : ''),
					'metaKeywords' => (!empty($_POST['metaKeywords'][$language->languageID]) ? $_POST['metaKeywords'][$language->languageID] : '')
				];
			}
		}
		else {
			$content[0] = [
				'customURL' => (!empty($_POST['customURL'][0]) ? $_POST['customURL'][0] : ''),
				'title' => (!empty($_POST['title'][0]) ? $_POST['title'][0] : ''),
				'content' => (!empty($_POST['content'][0]) ? $parseHTML($_POST['content'][0]) : ''),
				'metaDescription' => (!empty($_POST['metaDescription'][0]) ? $_POST['metaDescription'][0] : ''),
				'metaKeywords' => (!empty($_POST['metaKeywords'][0]) ? $_POST['metaKeywords'][0] : '')
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
			'identifier' => 'com.woltlab.wcf.generic'.$pageEditor->pageID
		]);
		
		if ($this->isLandingPage) {
			$page->setAsLandingPage();
		}
		
		// call saved event
		$this->saved();
		
		// forward to page list
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('PageList'));
		exit;
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
			'pageNodeList' => (new PageNodeTree())->getNodeList()
		]);
	}
}
