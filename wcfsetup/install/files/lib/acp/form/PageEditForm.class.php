<?php
namespace wcf\acp\form;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the page add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
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
	 * @var integer
	 */
	public $pageID = 0;
	
	/**
	 * page object
	 * @var Page
	 */
	public $page = null;
	
	/**
	 * @inheritDoc
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
	protected function validateName() {
		if (mb_strtolower($this->name) != mb_strtolower($this->page->name)) {
			parent::validateName();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateParentPageID() {
		if (!$this->page->controller && $this->parentPageID) {
			$page = new Page($this->parentPageID);
			if (!$page->pageID) {
				throw new UserInputException('parentPageID', 'invalid');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validatePackageID() {
		if (!$this->page->controller) {
			parent::validatePackageID();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		if ($this->page->controller) {
			$this->objectAction = new PageAction(array($this->page), 'update', array('data' => array_merge($this->additionalFields, array(
				'name' => $this->name,
				'isDisabled' => ($this->isDisabled) ? 1 : 0,
				'isLandingPage' => ($this->isLandingPage) ? 1 : 0,
				'controllerCustomURL' => (!empty($_POST['customURL'][0]) ? $_POST['customURL'][0] : ''),
				'lastUpdateTime' => TIME_NOW
			))));
			$this->objectAction->executeAction();
		}
		else {
			$content = array();
			if ($this->isMultilingual) {
				foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
					$content[$language->languageID] = array(
						'customURL' => (!empty($_POST['customURL'][$language->languageID]) ? $_POST['customURL'][$language->languageID] : ''),
						'title' => (!empty($_POST['title'][$language->languageID]) ? $_POST['title'][$language->languageID] : ''),
						'content' => (!empty($_POST['content'][$language->languageID]) ? $_POST['content'][$language->languageID] : ''),
						'metaDescription' => (!empty($_POST['metaDescription'][$language->languageID]) ? $_POST['metaDescription'][$language->languageID] : ''),
						'metaKeywords' => (!empty($_POST['metaKeywords'][$language->languageID]) ? $_POST['metaKeywords'][$language->languageID] : '')
					);
				}
			}
			else {
				$content[0] = array(
					'customURL' => (!empty($_POST['customURL'][0]) ? $_POST['customURL'][0] : ''),
					'title' => (!empty($_POST['title'][0]) ? $_POST['title'][0] : ''),
					'content' => (!empty($_POST['content'][0]) ? $_POST['content'][0] : ''),
					'metaDescription' => (!empty($_POST['metaDescription'][0]) ? $_POST['metaDescription'][0] : ''),
					'metaKeywords' => (!empty($_POST['metaKeywords'][0]) ? $_POST['metaKeywords'][0] : '')
				);
			}
			
			$this->objectAction = new PageAction(array($this->page), 'update', array('data' => array_merge($this->additionalFields, array(
				'parentPageID' => ($this->parentPageID ?: null),
				'name' => $this->name,
				'isDisabled' => ($this->isDisabled) ? 1 : 0,
				'isLandingPage' => ($this->isLandingPage) ? 1 : 0,
				'packageID' => ($this->packageID ?: null),
				'lastUpdateTime' => TIME_NOW,
				'isMultilingual' => $this->isMultilingual
			)), 'content' => $content));
			$this->objectAction->executeAction();
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
			$this->packageID = $this->page->packageID;
			if ($this->page->isLandingPage) $this->isLandingPage = 1;
			if ($this->page->isDiabled) $this->isDisabled = 1;
			
			foreach ($this->page->getPageContent() as $languageID => $content) {
				$this->title[$languageID] = $content['title'];
				$this->content[$languageID] = $content['content'];
				$this->metaDescription[$languageID] = $content['metaDescription'];
				$this->metaKeywords[$languageID] = $content['metaKeywords'];
				$this->customURL[$languageID] = $content['customURL'];
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'pageID' => $this->pageID,
			'page' => $this->page
		));
	}
}
