<?php
namespace wcf\acp\form;
use wcf\data\application\Application;
use wcf\data\application\ApplicationAction;
use wcf\data\application\ViewableApplication;
use wcf\data\package\PackageCache;
use wcf\system\application\ApplicationHandler;
use wcf\form\AbstractForm;
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
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class ApplicationEditForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
	/**
	 * viewable application object
	 * @var	wcf\data\application\ViewableApplication
	 */
	public $application = null;
	
	/**
	 * cookie domain
	 * @var	string
	 */
	public $cookieDomain = '';
	
	/**
	 * cookie path
	 * @var	string
	 */
	public $cookiePath = '';
	
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
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canManageApplication');
	
	/**
	 * application package id
	 * @var	integer
	 */
	public $packageID = 0;
	
	/**
	 * @see	wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'applicationEdit';
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->packageID = intval($_REQUEST['id']);
		$this->application = new ViewableApplication(new Application($this->packageID));
		if (!$this->application->packageID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['cookieDomain'])) $this->cookieDomain = StringUtil::trim($_POST['cookieDomain']);
		if (isset($_POST['cookiePath'])) $this->cookiePath = StringUtil::trim($_POST['cookiePath']);
		if (isset($_POST['domainName'])) $this->domainName = StringUtil::trim($_POST['domainName']);
		if (isset($_POST['domainPath'])) $this->domainPath = StringUtil::trim($_POST['domainPath']);
	}
	
	/**
	 * @see	wcf\page\IForm::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->cookieDomain = $this->application->cookieDomain;
			$this->cookiePath = $this->application->cookiePath;
			$this->domainName = $this->application->domainName;
			$this->domainPath = $this->application->domainPath;
		}
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
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
			
			// check if cookie domain shares the same domain (may exclude subdomains)
			if (!StringUtil::endsWith($this->domainName, $this->cookieDomain)) {
				throw new UserInputException('cookieDomain', 'notValid');
			}
		}
		
		if (empty($this->domainPath)) {
			$this->cookiePath = '';
		}
		else {
			// strip first and last slash
			$this->domainPath = FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($this->domainPath));
			$this->cookiePath = FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($this->cookiePath));
			
			if (!empty($this->cookiePath) && ($this->domainPath != $this->cookiePath)) {
				// check if cookie path is contained within domain path
				if (!StringUtil::startsWith($this->domainPath, $this->cookiePath)) {
					throw new UserInputException('cookiePath', 'notValid');
				}
			}
		}
		
		// add slashes
		$this->domainPath = FileUtil::addLeadingSlash(FileUtil::addTrailingSlash($this->domainPath));
		$this->cookiePath = FileUtil::addLeadingSlash(FileUtil::addTrailingSlash($this->cookiePath));
		
		// search for other applications with the same domain and path
		$sql = "SELECT	packageID
			FROM	wcf".WCF_N."_application
			WHERE	domainName = ?
				AND domainPath = ?
				AND packageID <> ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute(array(
			$this->domainName,
			$this->domainPath,
			$this->application->packageID
		));
		$row = $statement->fetchArray();
		if ($row) {
			WCF::getTPL()->assign('conflictApplication', PackageCache::getInstance()->getPackage($row['packageID']));
			throw new UserInputException('domainPath', 'conflict');
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save application
		$this->objectAction = new ApplicationAction(array($this->application->getDecoratedObject()), 'update', array('data' => array(
			'cookieDomain' => $this->cookieDomain,
			'cookiePath' => $this->cookiePath,
			'domainName' => $this->domainName,
			'domainPath' => $this->domainPath
		)));
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// re-calculate cookie settings
		ApplicationHandler::rebuild();
		
		// show success.
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'application' => $this->application,
			'cookieDomain' => $this->cookieDomain,
			'cookiePath' => $this->cookiePath,
			'domainName' => $this->domainName,
			'domainPath' => $this->domainPath,
			'packageID' => $this->packageID
		));
	}
}
