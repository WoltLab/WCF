<?php
namespace wcf\system;
use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\CacheHandler;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\session\SessionFactory;
use wcf\system\session\SessionHandler;
use wcf\system\template\TemplateEngine;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\exception;
use wcf\util;

// try to disable execution time limit
@set_time_limit(0);

// define current wcf version
define('WCF_VERSION', '2.0.0 Alpha 1 (Maelstrom)');

// define current unix timestamp
define('TIME_NOW', time());

// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/core.functions.php');
}

/**
 * WCF is the central class for the community framework.
 * It holds the database connection, access to template and language engine.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category 	Community Framework
 */
class WCF {
	/**
	 * list of autoload directories
	 * @var array
	 */
	protected static $autoloadDirectories = array();
	
	/**
	 * list of unique instances of each core object
	 * @var	array<wcf\system\SingletonFactory>
	 */
	protected static $coreObject = array();
	
	/**
	 * list of cached core objects
	 * @var	array<array>
	 */
	protected static $coreObjectCache = array();
	
	/**
	 * list of ids of dependent packages
	 * @var	array<integer>
	 */	
	protected static $packageDependencies = array();
	
	/**
	 * database object
	 * @var wcf\system\database\Database
	 */
	protected static $dbObj = null;
	
	/**
	 * language object
	 * @var wcf\system\language\Language
	 */
	protected static $languageObj = null;
	
	/**
	 * session object
	 * @var wcf\system\session\SessionHandler
	 */
	protected static $sessionObj = null;
	
	/**
	 * template object
	 * @var wcf\system\template\TemplateEngine
	 */
	protected static $tplObj = null;
	
	/**
	 * current user object
	 * @var wcf\data\user\User
	 */
	protected static $userObj = null;
	
	/**
	 * Calls all init functions of the WCF class.
	 */
	public function __construct() {
		// add autoload directory
		self::$autoloadDirectories['wcf'] = WCF_DIR . 'lib/';
		
		// define tmp directory
		if (!defined('TMP_DIR')) define('TMP_DIR', util\FileUtil::getTempFolder());
		
		// start initialization
		$this->initMagicQuotes();
		$this->initDB();
		$this->loadOptions();
		$this->initCache();
		$this->initSession();
		$this->initLanguage();
		$this->initTPL();
		$this->initBlacklist();
		$this->initCoreObjects();
		$this->initApplications();
	}
	
	/**
	 * Replacement of the "__destruct()" method.
	 * Seems that under specific conditions (windows) the destructor is not called automatically.
	 * So we use the php register_shutdown_function to register an own destructor method.
	 * Flushs the output, updates the session and executes the shutdown queries.
	 */
	public static function destruct() {
		// flush ouput
		if (ob_get_level() && ini_get('output_handler')) {
			ob_flush();
		}
		else {
			flush();
		}
		
		// update session
		if (is_object(self::getSession())) {
			self::getSession()->update();
		}
		
		// close cache source
		if (is_object(CacheHandler::getInstance()) && is_object(CacheHandler::getInstance()->getCacheSource())) {
			CacheHandler::getInstance()->getCacheSource()->close();
		}
		
		// execute shutdown actions of user storage handler
		UserStorageHandler::getInstance()->shutdown();
	}
	
	/**
	 * Removes slashes in superglobal gpc data arrays if 'magic quotes gpc' is enabled.
	 */
	protected function initMagicQuotes() {
		if (function_exists('get_magic_quotes_gpc')) {
			if (@get_magic_quotes_gpc()) {
				if (count($_REQUEST)) {
					$_REQUEST = util\ArrayUtil::stripslashes($_REQUEST);
				}
				if (count($_POST)) {
					$_POST = util\ArrayUtil::stripslashes($_POST);
				}
				if (count($_GET)) {
					$_GET = util\ArrayUtil::stripslashes($_GET);
				}
				if (count($_COOKIE)) {
					$_COOKIE = util\ArrayUtil::stripslashes($_COOKIE);
				}
				if (count($_FILES)) {
					foreach ($_FILES as $name => $attributes) {
						foreach ($attributes as $key => $value) {
							if ($key != 'tmp_name') {
								$_FILES[$name][$key] = util\ArrayUtil::stripslashes($value);
							}
						}
					}
				}
			}
		}
	
		if (function_exists('set_magic_quotes_runtime')) {
			@set_magic_quotes_runtime(0);
		}
	}
	
	/**
	 * Returns the database object.
	 *
	 * @return	wcf\system\database\Database
	 */
	public static final function getDB() {
		return self::$dbObj;
	}
	
	/**
	 * Returns the session object.
	 *
	 * @return	wcf\system\session\SessionHandler
	 */
	public static final function getSession() {
		return self::$sessionObj;
	}
	
	/**
	 * Returns the user object.
	 *
	 * @return	wcf\data\user\User
	 */
	public static final function getUser() {
		return self::$userObj;
	}
	
	/**
	 * Returns the language object.
	 *
	 * @return 	wcf\data\language\Language
	 */
	public static final function getLanguage() {
		return self::$languageObj;
	}
	
	/**
	 * Returns the template object.
	 *
	 * @return	wcf\system\template\TemplateEngine
	 */
	public static final function getTPL() {
		return self::$tplObj;
	}
	
	/**
	 * Calls the show method on the given exception.
	 *
	 * @param	\Exception	$e
	 */
	public static final function handleException(\Exception $e) {
		if ($e instanceof exception\IPrintableException) {
			$e->show();
			exit;
		}
		
		print $e;
		exit;
	}
	
	/**
	 * Catches php errors and throws instead a system exception.
	 *
	 * @param	integer		$errorNo
	 * @param	string		$message
	 * @param	string		$filename
	 * @param	integer		$lineNo
	 */
	public static final function handleError($errorNo, $message, $filename, $lineNo) {
		if (error_reporting() != 0) {
			$type = 'error';
			switch ($errorNo) {
				case 2: $type = 'warning';
					break;
				case 8: $type = 'notice';
					break;
			}
			
			throw new exception\SystemException('PHP '.$type.' in file '.$filename.' ('.$lineNo.'): '.$message, 0);
		}
	}
	
	/**
	 * Loads the database configuration and creates a new connection to the database.
	 */
	protected function initDB() {
		// get configuration
		$dbHost = $dbUser = $dbPassword = $dbName = '';
		$dbPort = 0;
		$dbClass = 'wcf\system\database\MySQLDatabase';
		require(WCF_DIR.'config.inc.php');
		
		// create database connection
		self::$dbObj = new $dbClass($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);
	}
	
	/**
	 * Initialises the cache handler and loads the default cache resources.
	 */
	protected function initCache() {
		$this->loadDefaultCacheResources();
	}
	
	/**
	 * Loads the default cache resources.
	 */
	protected function loadDefaultCacheResources() {
		CacheHandler::getInstance()->addResource(
			'languages',
			WCF_DIR.'cache/cache.languages.php',
			'wcf\system\cache\builder\LanguageCacheBuilder'
		);
		CacheHandler::getInstance()->addResource(
			'spiders',
			WCF_DIR.'cache/cache.spiders.php',
			'wcf\system\cache\builder\SpiderCacheBuilder'
		);
		
		if (defined('PACKAGE_ID')) {
			CacheHandler::getInstance()->addResource(
				'coreObjects-'.PACKAGE_ID,
				WCF_DIR.'cache/cache.coreObjects-'.PACKAGE_ID.'.php',
				'wcf\system\cache\builder\CoreObjectCacheBuilder'
			);
		}
	}
	
	/**
	 * Includes the options file.
	 * If the option file doesn't exist, the rebuild of it is started.
	 * 
	 * @param	string		$filename
	 */
	protected function loadOptions($filename = null, $packageID = 1) {
		if ($filename === null) $filename = WCF_DIR.'options.inc.php';
		
		// create options file if doesn't exist
		if (!file_exists($filename) || filemtime($filename) <= 1) {
			\wcf\data\option\OptionEditor::rebuildFile($filename, $packageID);
		}
		require_once($filename);
	}
	
	/**
	 * Starts the session system.
	 */
	protected function initSession() {
		$factory = new SessionFactory();
		$factory->load();
		
		self::$sessionObj = SessionHandler::getInstance();
		self::$userObj = self::getSession()->getUser();
	}
	
	/**
	 * Initialises the language engine.
	 */
	protected function initLanguage() {
		if (isset($_GET['l']) && !self::getUser()->userID) {
			self::getSession()->setLanguageID(intval($_GET['l']));
		}
		
		// set mb settings
		mb_internal_encoding('UTF-8');
		if (function_exists('mb_regex_encoding')) mb_regex_encoding('UTF-8');
		mb_language('uni');
		
		// get language
		self::$languageObj = LanguageFactory::getInstance()->getUserLanguage(self::getSession()->getLanguageID());
		self::$languageObj->setLocale();
	}
	
	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		self::$tplObj = TemplateEngine::getInstance();
		self::getTPL()->setLanguageID(self::getLanguage()->languageID);
		$this->assignDefaultTemplateVariables();
	}
	
	/**
	 * Executes the blacklist.
	 */
	protected function initBlacklist() {
		if (defined('BLACKLIST_IP_ADDRESSES') && BLACKLIST_IP_ADDRESSES != '') {
			if (!util\StringUtil::executeWordFilter(WCF::getSession()->ipAddress, BLACKLIST_IP_ADDRESSES)) {
				throw new exception\PermissionDeniedException();
			}
		}
		if (defined('BLACKLIST_USER_AGENTS') && BLACKLIST_USER_AGENTS != '') {
			if (!util\StringUtil::executeWordFilter(WCF::getSession()->userAgent, BLACKLIST_USER_AGENTS)) {
				throw new exception\PermissionDeniedException();
			}
		}
		if (defined('BLACKLIST_HOSTNAMES') && BLACKLIST_HOSTNAMES != '') {
			if (!util\StringUtil::executeWordFilter(@gethostbyaddr(WCF::getSession()->ipAddress), BLACKLIST_HOSTNAMES)) {
				throw new exception\PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Initializes applications.
	 */
	protected function initApplications() {
		if (PACKAGE_ID == 1) return;
		
		// start main application
		$application = ApplicationHandler::getInstance()->getActiveApplication();
		$this->loadApplication($application);
		
		// start dependent applications
		$applications = ApplicationHandler::getInstance()->getDependentApplications();
		foreach ($applications as $application) {
			$this->loadApplication($application, true);
		}
	}
	
	/**
	 * Loads an application.
	 *
	 * @param	wcf\data\application\Application		$application
	 * @param	boolean						$isDependentApplication
	 */	
	protected function loadApplication(Application $application, $isDependentApplication = false) {
		$package = PackageCache::getInstance()->getPackage($application->packageID);
		
		$abbreviation = ApplicationHandler::getInstance()->getAbbreviation($application->packageID);
		$packageDir = util\FileUtil::getRealPath(WCF_DIR.$package->packageDir);
		self::$autoloadDirectories[$abbreviation] = $packageDir . 'lib/';
		
		$className = $abbreviation.'\system\\'.strtoupper($abbreviation).'Core';
		if (class_exists($className) && util\ClassUtil::isInstanceOf($className, 'wcf\system\application\IApplication')) {
			// include config file
			$configPath = $packageDir . PackageInstallationDispatcher::CONFIG_FILE;
			if (file_exists($configPath)) {
				require_once($configPath);
			}
			else {
				throw new exception\SystemException('Unable to load configuration for '.$package->package);
			}
			
			// start application if not within ACP
			if (!class_exists('wcf\system\WCFACP', false)) {
				new $className();
			}
		}
		else {
			unset(self::$autoloadDirectories[$abbreviation]);
			throw new exception\SystemException("Unable to run '".$package->package."', '".$className."' is missing or does not implement 'wcf\system\application\IApplication'.");
		}
		
		// register template path in ACP
		if (class_exists('wcf\system\WCFACP', false)) {
			$this->getTPL()->addTemplatePath($application->packageID, $packageDir . 'acp/templates/');
		}
		else if (!$isDependentApplication) {
			// load options
			$this->loadOptions($packageDir.'options.inc.php', $application->packageID);
			
			// assign base tag
			$this->getTPL()->assign('baseHref', $application->domainName . $application->domainPath);
		}
	}
	
	/**
	 * Initializes core object cache.
	 */
	protected function initCoreObjects() {
		// ignore core objects if installing WCF
		if (PACKAGE_ID == 0) {
			return;
		}
		
		self::$coreObjectCache = CacheHandler::getInstance()->get('coreObjects-'.PACKAGE_ID);
		self::$packageDependencies = \wcf\system\package\PackageDependencyHandler::getDependencies();
	}
	
	/**
	 * Assigns some default variables to the template engine.
	 */
	protected function assignDefaultTemplateVariables() {
		self::getTPL()->registerPrefilter(array('event', 'hascontent', 'lang', 'icon'));
		self::getTPL()->assign(array('__wcf' => $this));
	}
	
	/**
	 * Wrapper for the getter methods of this class.
	 *
	 * @param	string		$name
	 * @return	mixed		value
	 */
	public function __get($name) {
		$method = 'get'.ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		
		throw new exception\SystemException("method '".$method."' does not exist in class WCF");
	}
	
	/**
	 * Changes the active language.
	 *
	 * @param	integer		$languageID
	 */
	public static final function setLanguage($languageID) {
		self::$languageObj = LanguageFactory::getInstance()->getLanguage($languageID);
		self::getTPL()->setLanguageID(self::getLanguage()->languageID);
	}
	
	/**
	 * Includes the required util or exception classes automatically.
	 *
	 * @param 	string		$className
	 * @see		spl_autoload_register()
	 */
	public static final function autoload($className) {
		$namespaces = explode('\\', $className);
		if (count($namespaces) > 1) {
			$applicationPrefix = array_shift($namespaces);
			if (isset(self::$autoloadDirectories[$applicationPrefix])) {
				$classPath = self::$autoloadDirectories[$applicationPrefix] . implode('/', $namespaces) . '.class.php';
				if (file_exists($classPath)) {
					require_once($classPath);
				}
			}
		}
	}
	
	/**
	 * @see	wcf\system\WCF::__callStatic()
	 */
	public final function __call($name, array $arguments) {
		// bug fix to avoid php crash, see http://bugs.php.net/bug.php?id=55020
		if (!method_exists($this, $name)) {
			return self::__callStatic($name, $arguments);
		}
		
		return $this->$name($arguments);
	}
	
	/**
	 * Returns dynamically loaded core objects.
	 *
	 * @param	string		$name
	 * @param	array		$arguments
	 */
	public static final function __callStatic($name, array $arguments) {
		$className = preg_replace('~^get~', '', $name);
		
		if (isset(self::$coreObject[$className])) {
			return self::$coreObject[$className];
		}
		
		$objectName = self::getCoreObject($className);
		if ($objectName === null) {
			throw new exception\SystemException("Core object '".$className."' is unknown.");
		}
		
		if (class_exists($objectName)) {
			if (!(util\ClassUtil::isInstanceOf($objectName, 'wcf\system\SingletonFactory'))) {
				throw new exception\SystemException("class '".$objectName."' does not implement the interface 'SingletonFactory'");
			}
			
			self::$coreObject[$className] = call_user_func(array($objectName, 'getInstance'));
			return self::$coreObject[$className];
		}
	}
	
	/**
	 * Searches for cached core object definition.
	 *
	 * @param	string		$className
	 * @return	string
	 */
	protected static final function getCoreObject($className) {
		foreach (self::$packageDependencies as $packageID) {
			if (isset(self::$coreObjectCache[$packageID][$className])) {
				return self::$coreObjectCache[$packageID][$className];
			}
		}
		
		return null;
	}
	
	/**
	 * Returns true if the debug mode is enabled, otherwise false.
	 * 
	 * @return boolean
	 */
	public static function debugModeIsEnabled() {
		if (defined('ENABLE_DEBUG_MODE') && ENABLE_DEBUG_MODE) return true;
		return false;
	}
	
	/**
	 * Returns true if benchmarking is enabled, otherwise false.
	 * 
	 * @return boolean
	 */
	public static function benchmarkIsEnabled() {
		// benchmarking is enabled by default
		if (!defined('ENABLE_BENCHMARK') || ENABLE_BENCHMARK) return true;
		return false;
	}
}
