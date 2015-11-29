<?php
namespace wcf\acp\form;
use wcf\data\application\ApplicationList;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\data\page\PageEditor;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the page add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
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
	 * @var boolean
	 */
	public $isMultilingual = 0;
	
	/**
	 * parent page id
	 * @var integer
	 */
	public $parentPageID = 0;
	
	/**
	 * page's display name
	 * @var string
	 */
	public $displayName = '';
	
	/**
	 * true if page is disabled
	 * @var boolean
	 */
	public $isDisabled = 0;
	
	/**
	 * true if page is landing page
	 * @var boolean
	 */
	public $isLandingPage = 0;
	
	/**
	 * package id of the page
	 * @var integer
	 */
	public $packageID = 1;
	
	/**
	 * list of available applications (standalone packages)
	 * @var Application[]
	 */
	public $availableApplications = [];
	
	/**
	 * page custom URL
	 * @var array<string>
	 */
	public $customURL = [];
	
	/**
	 * page titles
	 * @var array<string>
	 */
	public $title = [];
	
	/**
	 * page contents
	 * @var array<string>
	 */
	public $content = [];
	
	/**
	 * page meta descriptions
	 * @var array<string>
	 */
	public $metaDescription = [];
	
	/**
	 * page meta keywords
	 * @var array<string>
	 */
	public $metaKeywords = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (!empty($_REQUEST['isMultilingual'])) $this->isMultilingual = 1;
		
		// get available applications
		$applicationList = new ApplicationList();
		$applicationList->readObjects();
		$this->availableApplications = $applicationList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['parentPageID'])) $this->parentPageID = intval($_POST['parentPageID']);
		if (isset($_POST['displayName'])) $this->displayName = StringUtil::trim($_POST['displayName']);
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
		if (isset($_POST['isLandingPage'])) $this->isLandingPage = 1;
		if (isset($_POST['packageID'])) $this->packageID = intval($_POST['packageID']);
		
		if (isset($_POST['customURL']) && is_array($_POST['customURL'])) $this->customURL = ArrayUtil::trim($_POST['customURL']);
		if (isset($_POST['title']) && is_array($_POST['title'])) $this->title = ArrayUtil::trim($_POST['title']);
		if (isset($_POST['content']) && is_array($_POST['content'])) $this->content = ArrayUtil::trim($_POST['content']);
		if (isset($_POST['metaDescription']) && is_array($_POST['metaDescription'])) $this->metaDescription = ArrayUtil::trim($_POST['metaDescription']);
		if (isset($_POST['metaKeywords']) && is_array($_POST['metaKeywords'])) $this->metaKeywords = ArrayUtil::trim($_POST['metaKeywords']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		$this->validateDisplayName();
		
		$this->validateParentPageID();
		
		$this->validatePackageID();
		
		$this->validateCustomUrl();
	}
	
	protected function validateDisplayName() {
		if (empty($this->displayName)) {
			throw new UserInputException('displayName');
		}
		if (Page::getPageByDisplayName($this->displayName)) {
			throw new UserInputException('displayName', 'notUnique');
		}
	}
	
	protected function validateParentPageID() {
		if ($this->parentPageID) {
			$page = new Page($this->parentPageID);
			if (!$page->pageID) {
				throw new UserInputException('parentPageID', 'invalid');
			}
		}
	}
	
	protected function validatePackageID() {
		if (!isset($this->availableApplications[$this->packageID])) {
			throw new UserInputException('packageID', 'invalid');
		}
	}
	
	protected function validateCustomUrl() {
		foreach ($this->customURL as $type => $customURL) {
			if (!empty($customURL) && !RouteHandler::isValidCustomUrl($customURL)) {
				throw new UserInputException('customURL_' . $type, 'invalid');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$content = [];
		if ($this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'customURL' => (!empty($_POST['customURL'][$language->languageID]) ? $_POST['customURL'][$language->languageID] : ''),
					'title' => (!empty($_POST['title'][$language->languageID]) ? $_POST['title'][$language->languageID] : ''),
					'content' => (!empty($_POST['content'][$language->languageID]) ? $_POST['content'][$language->languageID] : ''),
					'metaDescription' => (!empty($_POST['metaDescription'][$language->languageID]) ? $_POST['metaDescription'][$language->languageID] : ''),
					'metaKeywords' => (!empty($_POST['metaKeywords'][$language->languageID]) ? $_POST['metaKeywords'][$language->languageID] : '')
				];
			}
		}
		else {
			$content[0] = [
				'customURL' => (!empty($_POST['customURL'][0]) ? $_POST['customURL'][0] : ''),
				'title' => (!empty($_POST['title'][0]) ? $_POST['title'][0] : ''),
				'content' => (!empty($_POST['content'][0]) ? $_POST['content'][0] : ''),
				'metaDescription' => (!empty($_POST['metaDescription'][0]) ? $_POST['metaDescription'][0] : ''),
				'metaKeywords' => (!empty($_POST['metaKeywords'][0]) ? $_POST['metaKeywords'][0] : '')
			];
		}
		
		$this->objectAction = new PageAction([], 'create', ['data' => array_merge($this->additionalFields, [
			'parentPageID' => ($this->parentPageID ?: null),
			'displayName' => $this->displayName,
			'isDisabled' => ($this->isDisabled) ? 1 : 0,
			'isLandingPage' => ($this->isLandingPage) ? 1 : 0,
			'packageID' => ($this->packageID ?: null),
			'lastUpdateTime' => TIME_NOW,
			'isMultilingual' => $this->isMultilingual,
			'name' => ''
		]), 'content' => $content]);
		$returnValues = $this->objectAction->executeAction();
		// set generic page name
		$pageEditor = new PageEditor($returnValues['returnValues']);
		$pageEditor->update([
			'name' => 'com.woltlab.wcf.generic'.$pageEditor->pageID
		]);
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
		
		// reset variables
		$this->parentPageID = $this->isDisabled = $this->isLandingPage = 0;
		$this->packageID = 1;
		$this->displayName = '';
		$this->customURL = $this->title = $this->content = $this->metaDescription = $this->metaKeywords = [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$pageNodeList = new PageNodeTree();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'parentPageID' => $this->parentPageID,
			'displayName' => $this->displayName,
			'isDisabled' => $this->isDisabled,
			'isLandingPage' => $this->isLandingPage,
			'isMultilingual' => $this->isMultilingual,
			'packageID' => $this->packageID,
			'customURL' => $this->customURL,
			'title' => $this->title,
			'content' => $this->content,
			'metaDescription' => $this->metaDescription,
			'metaKeywords' => $this->metaKeywords,
			'availableApplications' => $this->availableApplications,
			'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
			'pageNodeList' => $pageNodeList->getNodeList()
		]);
	}
}
