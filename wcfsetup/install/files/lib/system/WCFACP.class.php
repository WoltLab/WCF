<?php
namespace wcf\system;
use wcf\system\cache\CacheHandler;
use wcf\system\request\RouteHandler;
use wcf\system\session\ACPSessionFactory;
use wcf\system\session\SessionHandler;
use wcf\system\template\ACPTemplateEngine;
use wcf\util;

/**
 * Extends WCF class with functions for the admin control panel.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category 	Community Framework
 */
class WCFACP extends WCF {
	/**
	 * Calls all init functions of the WCF and the WCFACP class. 
	 */
	public function __construct() {
		// add autoload directory
		self::$autoloadDirectories['wcf'] = WCF_DIR . 'lib/';
		
		// define tmp directory
		if (!defined('TMP_DIR')) define('TMP_DIR', util\BasicFileUtil::getTempFolder());
		
		// start initialization
		$this->initMagicQuotes();
		$this->initDB();
		$this->initPackage();
		$this->loadOptions();
		$this->initCache();
		$this->initSession();
		$this->initLanguage();
		$this->initTPL();
		$this->initBlacklist();
		$this->initAuth();
		$this->initCoreObjects();
		
		// prevent application loading during setup
		if (PACKAGE_ID) {
			$this->initApplications();
		}
	}
	
	/**
	 * Does the user authentication.
	 */
	protected function initAuth() {
		// this is a work-around since neither RequestHandler
		// nor RouteHandler are populated right now
		$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '';
		if (empty($pathInfo) || !preg_match('~^/(ACPCaptcha|Login)/~', $pathInfo)) {
			if (WCF::getUser()->userID == 0) {
				util\HeaderUtil::redirect('index.php/Login/'.SID_ARG_1ST);
				exit;
			}
			else {
				WCF::getSession()->checkPermissions(array('admin.general.canUseAcp'));
			}
		}
	}
	
	/**
	 * @see	wcf\system\WCF::initSession()
	 */
	protected function initSession() {
		$factory = new ACPSessionFactory();
		$factory->load();
		
		self::$sessionObj = SessionHandler::getInstance();
		self::$userObj = self::getSession()->getUser();
	}
	
	/**
	 * @see	wcf\system\WCF::initTPL()
	 */
	protected function initTPL() {
		self::$tplObj = ACPTemplateEngine::getInstance();
		self::getTPL()->setLanguageID(self::getLanguage()->languageID);
		$this->assignDefaultTemplateVariables();
	}
	
	/**
	 * @see wcf\system\WCF::assignDefaultTemplateVariables()
	 */
	protected function assignDefaultTemplateVariables() {
		parent::assignDefaultTemplateVariables();
		
		// base tag is determined on runtime
		$host = RouteHandler::getHost();
		$path = RouteHandler::getPath();
		
		self::getTPL()->assign(array(
			'baseHref' => $host . $path,
			'quickAccessPackages' => $this->getQuickAccessPackages(),
			//'timezone' => util\DateUtil::getTimezone()
		));
	}
	
	/**
	 * @see WCF::loadDefaultCacheResources()
	 */
	protected function loadDefaultCacheResources() {
		parent::loadDefaultCacheResources();
		
		CacheHandler::getInstance()->addResource(
			'packages',
			WCF_DIR.'cache/cache.packages.php',
			'wcf\system\cache\builder\PackageCacheBuilder'
		);
	}
	
	/**
	 * Initialises the active package.
	 */
	protected function initPackage() {
		// define active package id
		if (!defined('PACKAGE_ID')) {
			$packageID = self::getWcfPackageID();
			define('PACKAGE_ID', $packageID);
		}
		
		/*
		$packageID = 0;
		$packages = CacheHandler::getInstance()->get('packages');
		if (isset($_REQUEST['packageID'])) $packageID = intval($_REQUEST['packageID']);
		
		if (!isset($packages[$packageID]) || !$packages[$packageID]['isApplication']) {
			// package id is invalid
			$packageID = self::getWcfPackageID();
		}
		
		// define active package id
		if (!defined('PACKAGE_ID')) define('PACKAGE_ID', $packageID);*/ 
	}
	
	/**
	 * Returns the package id of the wcf package.
	 * 
	 * @return	integer
	 */
	public static final function getWcfPackageID() {
		// try to find package wcf id
		$sql = "SELECT	packageID
			FROM	wcf".WCF_N."_package
			WHERE	package = 'com.woltlab.wcf'";
		$statement = WCFACP::getDB()->prepareStatement($sql);
		$statement->execute();
		$package = $statement->fetchArray();
		
		if (!$package) return 0;
		else return $package['packageID'];
	}
	
	/**
	 * Returns a list of all installed applications packages.
	 * 
	 * @return	array
	 */
	protected function getQuickAccessPackages() {
		$quickAccessPackages = array();
		$packages = CacheHandler::getInstance()->get('packages');
		foreach ($packages as $packageID => $package) {
			if (!$package->isApplication) break;
			if ($package->package != 'com.woltlab.wcf') {
				$quickAccessPackages[] = $package;
			}
		}
		
		return $quickAccessPackages;
	}
	
	/**
	 * Checks whether the active user has entered the valid master password.
	 */
	public static function checkMasterPassword() {
		if (defined('MODULE_MASTER_PASSWORD') && MODULE_MASTER_PASSWORD == 1 && !WCF::getSession()->getVar('masterPassword')) {
			if (file_exists(WCF_DIR.'acp/masterPassword.inc.php')) {
				require_once(WCF_DIR.'acp/masterPassword.inc.php');
			}
			if (defined('MASTER_PASSWORD') && defined('MASTER_PASSWORD_SALT')) {
				new \wcf\acp\form\MasterPasswordForm();
				exit;
			}
			else {
				new \wcf\acp\form\MasterPasswordInitForm();
				exit;
			}
		}
	}
}
