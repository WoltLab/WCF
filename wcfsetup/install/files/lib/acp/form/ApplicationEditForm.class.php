<?php
namespace wcf\acp\form;
use wcf\data\application\Application;
use wcf\data\application\ApplicationAction;
use wcf\data\application\ViewableApplication;
use wcf\data\package\PackageCache;
use wcf\data\page\Page;
use wcf\data\page\PageNodeTree;
use wcf\system\application\ApplicationHandler;
use wcf\form\AbstractForm;
use wcf\system\cache\builder\ApplicationCacheBuilder;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Shows the application edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class ApplicationEditForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
	/**
	 * viewable application object
	 * @var	ViewableApplication
	 */
	public $application;
	
	/**
	 * cookie domain
	 * @var	string
	 */
	public $cookieDomain = '';
	
	/**
	 * domain name
	 * @var	string
	 */
	public $domainName = '';
	
	/**
	 * domain path
	 * @var	string
	 */
	public $domainPath = '';
	
	/**
	 * landing page id
	 * @var integer
	 */
	public $landingPageID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.canManageApplication'];
	
	/**
	 * application package id
	 * @var	integer
	 */
	public $packageID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'applicationEdit';
	
	/**
	 * nested list of page nodes
	 * @var	\RecursiveIteratorIterator
	 */
	public $pageNodeList;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->packageID = intval($_REQUEST['id']);
		$this->application = new ViewableApplication(new Application($this->packageID));
		if (!$this->application->packageID) {
			throw new IllegalLinkException();
		}
		
		$this->pageNodeList = (new PageNodeTree())->getNodeList();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['cookieDomain'])) $this->cookieDomain = StringUtil::trim($_POST['cookieDomain']);
		if (isset($_POST['domainName'])) $this->domainName = StringUtil::trim($_POST['domainName']);
		if (isset($_POST['domainPath'])) $this->domainPath = StringUtil::trim($_POST['domainPath']);
		if (isset($_POST['landingPageID'])) $this->landingPageID = intval($_POST['landingPageID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->cookieDomain = $this->application->cookieDomain;
			$this->domainName = $this->application->domainName;
			$this->domainPath = $this->application->domainPath;
			$this->landingPageID = $this->application->landingPageID;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->domainName)) {
			throw new UserInputException('domainName');
		}
		else {
			$regex = new Regex('^https?\://');
			$this->domainName = FileUtil::removeTrailingSlash($regex->replace($this->domainName, ''));
			$this->cookieDomain = FileUtil::removeTrailingSlash($regex->replace($this->cookieDomain, ''));
			
			// domain may not contain path components
			$regex = new Regex('[/#\?&]');
			if ($regex->match($this->domainName)) {
				throw new UserInputException('domainName', 'containsPath');
			}
			else if ($regex->match($this->cookieDomain)) {
				throw new UserInputException('cookieDomain', 'containsPath');
			}
			
			// strip port from cookie domain
			$regex = new Regex(':[0-9]+$');
			$this->cookieDomain = $regex->replace($this->cookieDomain, '');
			
			// check if cookie domain shares the same domain (may exclude subdomains)
			if (!StringUtil::endsWith($regex->replace($this->domainName, ''), $this->cookieDomain)) {
				throw new UserInputException('cookieDomain', 'invalid');
			}
		}
		
		// add slashes
		$this->domainPath = FileUtil::addLeadingSlash(FileUtil::addTrailingSlash($this->domainPath));
		
		// search for other applications with the same domain and path
		$sql = "SELECT	packageID
			FROM	wcf".WCF_N."_application
			WHERE	domainName = ?
				AND domainPath = ?
				AND packageID <> ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([
			$this->domainName,
			$this->domainPath,
			$this->application->packageID
		]);
		$row = $statement->fetchArray();
		if ($row) {
			WCF::getTPL()->assign('conflictApplication', PackageCache::getInstance()->getPackage($row['packageID']));
			throw new UserInputException('domainPath', 'conflict');
		}
		
		if ($this->landingPageID) {
			$page = new Page($this->landingPageID);
			if (!$page->pageID) {
				throw new UserInputException('landingPageID');
			}
			else if ($page->requireObjectID || $page->excludeFromLandingPage || $page->isDisabled) {
				throw new UserInputException('landingPageID', 'invalid');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save application
		$this->objectAction = new ApplicationAction([$this->application->getDecoratedObject()], 'update', ['data' => array_merge($this->additionalFields, [
			'cookieDomain' => mb_strtolower($this->cookieDomain),
			'domainName' => mb_strtolower($this->domainName),
			'domainPath' => $this->domainPath,
			'landingPageID' => ($this->landingPageID ?: null)
		])]);
		$this->objectAction->executeAction();
		
		$this->saved();
		
		if ($this->application->packageID === 1) {
			if ($this->landingPageID) {
				(new Page($this->landingPageID))->setAsLandingPage();
			}
			else {
				$sql = "UPDATE  wcf".WCF_N."_page
					SET     isLandingPage = ?
					WHERE   isLandingPage = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					0,
					1
				]);
			}
		}
		
		// re-calculate cookie settings
		ApplicationHandler::rebuild();
		
		// reset caches to reflect new landing page
		ApplicationCacheBuilder::getInstance()->reset();
		RoutingCacheBuilder::getInstance()->reset();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'application' => $this->application,
			'cookieDomain' => $this->cookieDomain,
			'domainName' => $this->domainName,
			'domainPath' => $this->domainPath,
			'packageID' => $this->packageID,
			'pageNodeList' => $this->pageNodeList,
			'landingPageID' => $this->landingPageID
		]);
	}
}
