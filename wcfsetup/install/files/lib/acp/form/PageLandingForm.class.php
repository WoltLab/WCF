<?php
namespace wcf\acp\form;
use wcf\data\application\Application;
use wcf\data\application\ApplicationAction;
use wcf\data\application\ApplicationList;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

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
class PageLandingForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.page.landing';
	
	/**
	 * list of available applications
	 * @var	Application[]
	 */
	public $applications;
	
	/**
	 * landing page id per application package id
	 * @var	integer[]
	 */
	public $landingPages = [];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManagePage'];
	
	/**
	 * list of available pages
	 * @var	Page[]
	 */
	public $pages = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		// get available applications and pages
		$applicationList = new ApplicationList();
		$applicationList->readObjects();
		$this->applications = $applicationList->getObjects();
		
		$pageList = new PageList();
		$pageList->readObjects();
		foreach ($pageList as $page) {
			if (!isset($this->pages[$page->packageID])) $this->pages[$page->packageID] = [];
			
			$this->pages[$page->packageID][$page->pageID] = $page;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['landingPages']) && is_array($_POST['landingPages'])) $this->landingPages = ArrayUtil::toIntegerArray($_POST['landingPages']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		foreach ($this->applications as $packageID => $application) {
			if (empty($this->landingPages[$packageID])) {
				throw new UserInputException('landingPage');
			}
			
			// handle application default
			if ($this->landingPages[$packageID] === -1) {
				$this->landingPages[$packageID] = null;
				
				continue;
			}
			
			$page = new Page($this->landingPages[$packageID]);
			if (!$page->pageID) {
				throw new UserInputException('landingPage', 'notValid');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new ApplicationAction($this->applications, 'setLandingPage', ['landingPages' => $this->landingPages]);
		$this->objectAction->executeAction();
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'applications' => $this->applications,
			'pageNodeList' => (new PageNodeTree())->getNodeList()
		]);
	}
}
