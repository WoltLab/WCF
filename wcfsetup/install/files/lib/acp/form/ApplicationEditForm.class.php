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
 * @copyright	2001-2016 WoltLab GmbH
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
	 * @var	\wcf\data\application\ViewableApplication
	 */
	public $application = null;
	
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['cookieDomain'])) $this->cookieDomain = StringUtil::trim($_POST['cookieDomain']);
		if (isset($_POST['domainName'])) $this->domainName = StringUtil::trim($_POST['domainName']);
		if (isset($_POST['domainPath'])) $this->domainPath = StringUtil::trim($_POST['domainPath']);
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
				throw new UserInputException('cookieDomain', 'notValid');
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
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save application
		$this->objectAction = new ApplicationAction([$this->application->getDecoratedObject()], 'update', ['data' => array_merge($this->additionalFields, [
			'cookieDomain' => $this->cookieDomain,
			'domainName' => $this->domainName,
			'domainPath' => $this->domainPath
		])]);
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// re-calculate cookie settings
		ApplicationHandler::rebuild();
		
		// show success.
		WCF::getTPL()->assign([
			'success' => true
		]);
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
			'packageID' => $this->packageID
		]);
	}
}
