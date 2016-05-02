<?php
namespace wcf\acp\form;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the page add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 * @since	2.2
 */
class PageEditForm extends PageAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.page.list';
	
	/**
	 * page id
	 * @var	integer
	 */
	public $pageID = 0;
	
	/**
	 * page object
	 * @var	Page
	 */
	public $page;
	
	/**
	 * @inheritDoc
	 * 
	 * @throws      IllegalLinkException
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (isset($_REQUEST['id'])) $this->pageID = intval($_REQUEST['id']);
		$this->page = new Page($this->pageID);
		if (!$this->page->pageID) {
			throw new IllegalLinkException();
		}
		if ($this->page->isMultilingual) $this->isMultilingual = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readPageType() {
		// not required for editing
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->pageType = $this->page->pageType;
		if ($this->page->originIsSystem) {
			$this->parentPageID = $this->page->parentPageID;
			$this->applicationPackageID = $this->page->applicationPackageID;
		}
		
		if ($this->page->requireObjectID) {
			// pages that require an object id can never be set as landing page
			$this->isLandingPage = 0;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateName() {
		if (mb_strtolower($this->name) != mb_strtolower($this->page->name)) {
			parent::validateName();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validatePageType() {
		// type is immutable
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$data = [
			'name' => $this->name,
			'isDisabled' => ($this->isDisabled) ? 1 : 0,
			'lastUpdateTime' => TIME_NOW,
			'parentPageID' => ($this->parentPageID ?: null),
			'applicationPackageID' => $this->applicationPackageID
		];
		
		if ($this->pageType == 'system') {
			$data['controllerCustomURL'] = (!empty($_POST['customURL'][0]) ? $_POST['customURL'][0] : '');
			$this->objectAction = new PageAction([$this->page], 'update', [
				'data' => array_merge($this->additionalFields, $data),
				'boxToPage' => $this->getBoxToPage()
			]);
			$this->objectAction->executeAction();
		}
		else {
			$content = [];
			if ($this->page->isMultilingual) {
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
			
			$this->objectAction = new PageAction([$this->page], 'update', [
				'data' => array_merge($this->additionalFields, $data),
				'content' => $content,
				'boxToPage' => $this->getBoxToPage()
			]);
			$this->objectAction->executeAction();
		}
		
		if ($this->isLandingPage != $this->page->isLandingPage) {
			$this->page->setAsLandingPage();
		}
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
	
		if (empty($_POST)) {
			$this->name = $this->page->name;
			$this->parentPageID = $this->page->parentPageID;
			$this->pageType = $this->page->pageType;
			$this->applicationPackageID = $this->page->applicationPackageID;
			if ($this->page->controllerCustomURL) $this->customURL[0] = $this->page->controllerCustomURL;
			if ($this->page->isLandingPage) $this->isLandingPage = 1;
			if ($this->page->isDiabled) $this->isDisabled = 1;
			
			foreach ($this->page->getPageContent() as $languageID => $content) {
				$this->title[$languageID] = $content['title'];
				$this->content[$languageID] = $content['content'];
				$this->metaDescription[$languageID] = $content['metaDescription'];
				$this->metaKeywords[$languageID] = $content['metaKeywords'];
				$this->customURL[$languageID] = $content['customURL'];
			}
			
			$this->boxIDs = [];
			foreach ($this->availableBoxes as $box) {
				if ($box->visibleEverywhere) {
					if (!in_array($box->boxID, $this->page->getBoxIDs())) {
						$this->boxIDs[] = $box->boxID;
					}
				}
				else {
					if (in_array($box->boxID, $this->page->getBoxIDs())) {
						$this->boxIDs[] = $box->boxID;
					}
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'pageID' => $this->pageID,
			'page' => $this->page
		]);
	}
}
