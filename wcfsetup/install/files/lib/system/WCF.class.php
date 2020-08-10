<?php
namespace wcf\system;
use wcf\data\application\Application;
use wcf\data\option\OptionEditor;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\package\PackageEditor;
use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\page\CmsPage;
use wcf\system\application\ApplicationHandler;
use wcf\system\application\IApplication;
use wcf\system\box\BoxHandler;
use wcf\system\cache\builder\CoreObjectCacheBuilder;
use wcf\system\cache\builder\PackageUpdateCacheBuilder;
use wcf\system\cronjob\CronjobScheduler;
use wcf\system\database\MySQLDatabase;
use wcf\system\event\EventHandler;
use wcf\system\exception\AJAXException;
use wcf\system\exception\ErrorException;
use wcf\system\exception\IPrintableException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\registry\RegistryHandler;
use wcf\system\request\Request;
use wcf\system\request\RequestHandler;
use wcf\system\session\SessionFactory;
use wcf\system\session\SessionHandler;
use wcf\system\style\StyleHandler;
use wcf\system\template\EmailTemplateEngine;
use wcf\system\template\TemplateEngine;
use wcf\system\user\storage\UserStorageHandler;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

// try to set a time-limit to infinite
@set_time_limit(0);

// fix timezone warning issue
if (!@ini_get('date.timezone')) {
	@date_default_timezone_set('Europe/London');
}

// define current woltlab suite version
define('WCF_VERSION', '5.3.0 Alpha 1');

// define current API version
// @deprecated 5.2
define('WSC_API_VERSION', 2019);

// define current unix timestamp
define('TIME_NOW', time());

// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/core.functions.php');
	require_once(WCF_DIR.'lib/system/api/autoload.php');
}

/**
 * WCF is the central class for the WoltLab Suite Core.
 * It holds the database connection, access to template and language engine.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System
 */
class WCF {
	/**
	 * list of supported legacy API versions
	 * @var integer[]
	 * @deprecated 5.2
	 */
	private static $supportedLegacyApiVersions = [2017, 2018];
	
	/**
	 * list of currently loaded applications
	 * @var	Application[]
	 */
	protected static $applications = [];
	
	/**
	 * list of currently loaded application objects
	 * @var	IApplication[]
	 */
	protected static $applicationObjects = [];
	
	/**
	 * list of autoload directories
	 * @var	array
	 */
	protected static $autoloadDirectories = [];
	
	/**
	 * list of unique instances of each core object
	 * @var	SingletonFactory[]
	 */
	protected static $coreObject = [];
	
	/**
	 * list of cached core objects
	 * @var	string[]
	 */
	protected static $coreObjectCache = [];
	
	/**
	 * database object
	 * @var	MySQLDatabase
	 */
	protected static $dbObj;
	
	/**
	 * language object
	 * @var	\wcf\data\language\Language
	 */
	protected static $languageObj;
	
	/**
	 * overrides disabled debug mode
	 * @var	boolean
	 */
	protected static $overrideDebugMode = false;
	
	/**
	 * session object
	 * @var	SessionHandler
	 */
	protected static $sessionObj;
	
	/**
	 * template object
	 * @var	TemplateEngine
	 */
	protected static $tplObj;
	
	/**
	 * true if Zend Opcache is loaded and enabled
	 * @var	boolean
	 */
	protected static $zendOpcacheEnabled;
	
	/**
	 * force logout during destructor call
	 * @var boolean
	 */
	protected static $forceLogout = false;
	
	/**
	 * Calls all init functions of the WCF class.
	 */
	public function __construct() {
		// add autoload directory
		self::$autoloadDirectories['wcf'] = WCF_DIR . 'lib/';
		
		// define tmp directory
		if (!defined('TMP_DIR')) define('TMP_DIR', FileUtil::getTempFolder());
		
		// start initialization
		$this->initDB();
		$this->loadOptions();
		$this->initSession();
		$this->initLanguage();
		$this->initTPL();
		$this->initCronjobs();
		$this->initCoreObjects();
		$this->initApplications();
		$this->initBlacklist();
		
		EventHandler::getInstance()->fireAction($this, 'initialized');
	}
	
	/**
	 * Flushes the output, closes the session, performs background tasks and more.
	 * 
	 * You *must* not create output in here under normal circumstances, as it might get eaten
	 * when gzip is enabled.
	 */
	public static function destruct() {
		try {
			// database has to be initialized
			if (!is_object(self::$dbObj)) return;
			
			$debug = self::debugModeIsEnabled(true);
			if (!$debug) {
				// flush output
				if (ob_get_level()) ob_end_flush();
				flush();
				
				// close connection if using FPM
				if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
			}
			
			// update session
			if (is_object(self::getSession())) {
				if (self::$forceLogout) {
					// do logout
					self::getSession()->delete();
				}
				else {
					self::getSession()->update();
				}
			}
			
			// execute shutdown actions of storage handlers
			RegistryHandler::getInstance()->shutdown();
			UserStorageHandler::getInstance()->shutdown();
		}
		catch (\Exception $exception) {
			die("<pre>WCF::destruct() Unhandled exception: ".$exception->getMessage()."\n\n".$exception->getTraceAsString());
		}
	}
	
	/**
	 * Returns the database object.
	 * 
	 * @return	\wcf\system\database\Database
	 */
	public static final function getDB() {
		return self::$dbObj;
	}
	
	/**
	 * Returns the session object.
	 * 
	 * @return	SessionHandler
	 */
	public static final function getSession() {
		return self::$sessionObj;
	}
	
	/**
	 * Returns the user object.
	 * 
	 * @return	\wcf\data\user\User
	 */
	public static final function getUser() {
		return self::getSession()->getUser();
	}
	
	/**
	 * Returns the language object.
	 * 
	 * @return	\wcf\data\language\Language
	 */
	public static final function getLanguage() {
		return self::$languageObj;
	}
	
	/**
	 * Returns the template object.
	 * 
	 * @return	TemplateEngine
	 */
	public static final function getTPL() {
		return self::$tplObj;
	}
	
	/**
	 * Calls the show method on the given exception.
	 * 
	 * @param	\Exception	$e
	 */
	public static final function handleException($e) {
		// backwards compatibility
		if ($e instanceof IPrintableException) {
			$e->show();
			exit;
		}
		
		if (ob_get_level()) {
			// discard any output generated before the exception occurred, prevents exception
			// being hidden inside HTML elements and therefore not visible in browser output
			// 
			// ob_get_level() can return values > 1, if the PHP setting `output_buffering` is on
			while (ob_get_level()) ob_end_clean();
			
			// Some webservers are broken and will apply gzip encoding at all cost, but they fail
			// to set a proper `Content-Encoding` HTTP header and mess things up even more.
			// Especially the `identity` value appears to be unrecognized by some of them, hence
			// we'll just gzip the output of the exception to prevent them from tampering.
			// This part is copied from `HeaderUtil` in order to isolate the exception handler!
			if (defined('HTTP_ENABLE_GZIP') && HTTP_ENABLE_GZIP && !defined('HTTP_DISABLE_GZIP')) {
				if (function_exists('gzcompress') && !@ini_get('zlib.output_compression') && !@ini_get('output_handler') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
					if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
						@header('Content-Encoding: x-gzip');
					}
					else {
						@header('Content-Encoding: gzip');
					}
					
					ob_start(function($output) {
						$size = strlen($output);
						$crc = crc32($output);
						
						$newOutput = "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\xff";
						$newOutput .= substr(gzcompress($output, 1), 2, -4);
						$newOutput .= pack('V', $crc);
						$newOutput .= pack('V', $size);
						
						return $newOutput;
					});
				}
			}
		}
		
		@header('HTTP/1.1 503 Service Unavailable');
		try {
			\wcf\functions\exception\printThrowable($e);
		}
		catch (\Throwable $e2) {
			echo "<pre>An Exception was thrown while handling an Exception:\n\n";
			echo preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $e2);
			echo "\n\nwas thrown while:\n\n";
			echo preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $e);
			echo "\n\nwas handled.</pre>";
			exit;
		}
	}
	
	/**
	 * Turns PHP errors into an ErrorException.
	 * 
	 * @param	integer		$severity
	 * @param	string		$message
	 * @param	string		$file
	 * @param	integer		$line
	 * @throws	ErrorException
	 */
	public static final function handleError($severity, $message, $file, $line) {
		// this is necessary for the shut-up operator
		if (!(error_reporting() & $severity)) return;
		
		throw new ErrorException($message, 0, $severity, $file, $line);
	}
	
	/**
	 * Loads the database configuration and creates a new connection to the database.
	 */
	protected function initDB() {
		// get configuration
		$dbHost = $dbUser = $dbPassword = $dbName = '';
		$dbPort = 0;
		$defaultDriverOptions = [];
		require(WCF_DIR.'config.inc.php');
		
		// create database connection
		self::$dbObj = new MySQLDatabase($dbHost, $dbUser, $dbPassword, $dbName, $dbPort, false, false, $defaultDriverOptions);
	}
	
	/**
	 * Loads the options file, automatically created if not exists.
	 */
	protected function loadOptions() {
		// The attachment module is always enabled since 5.2.
		// https://github.com/WoltLab/WCF/issues/2531
		define('MODULE_ATTACHMENT', 1);
		
		// Users cannot react to their own content since 5.2.
		// https://github.com/WoltLab/WCF/issues/2975
		define('LIKE_ALLOW_FOR_OWN_CONTENT', 0);
		define('LIKE_ENABLE_DISLIKE', 0);
		
		// Thumbnails for attachments are already enabled since 5.3.
		// https://github.com/WoltLab/WCF/pull/3444
		define('ATTACHMENT_ENABLE_THUMBNAILS', 1);
		
		// User markings are always applied in sidebars since 5.3.
		// https://github.com/WoltLab/WCF/issues/3330
		define('MESSAGE_SIDEBAR_ENABLE_USER_ONLINE_MARKING', 1);
		
		// Password strength configuration is deprecated since 5.3.
		define('REGISTER_ENABLE_PASSWORD_SECURITY_CHECK', 0);
		define('REGISTER_PASSWORD_MIN_LENGTH', 0);
		define('REGISTER_PASSWORD_MUST_CONTAIN_LOWER_CASE', 8);
		define('REGISTER_PASSWORD_MUST_CONTAIN_UPPER_CASE', 0);
		define('REGISTER_PASSWORD_MUST_CONTAIN_DIGIT', 0);
		define('REGISTER_PASSWORD_MUST_CONTAIN_SPECIAL_CHAR', 0);

		// rel=nofollow is always applied to external link since 5.3
		// https://github.com/WoltLab/WCF/issues/3339
		define('EXTERNAL_LINK_REL_NOFOLLOW', 1);
		
		$filename = WCF_DIR.'options.inc.php';
		
		// create options file if doesn't exist
		if (!file_exists($filename) || filemtime($filename) <= 1) {
			OptionEditor::rebuild();
		}
		require($filename);
		
		// check if option file is complete and writable
		if (PACKAGE_ID) {
			if (!is_writable($filename)) {
				FileUtil::makeWritable($filename);
				
				if (!is_writable($filename)) {
					throw new SystemException("The option file '" . $filename . "' is not writable.");
				}
			}
			
			// check if a previous write operation was incomplete and force rebuilding
			if (!defined('WCF_OPTION_INC_PHP_SUCCESS')) {
				OptionEditor::rebuild();
				
				require($filename);
			}
			
			if (ENABLE_DEBUG_MODE) {
				self::$dbObj->enableDebugMode();
			}
		}
	}
	
	/**
	 * Starts the session system.
	 */
	protected function initSession() {
		$factory = new SessionFactory();
		$factory->load();
		
		self::$sessionObj = SessionHandler::getInstance();
		self::$sessionObj->setHasValidCookie($factory->hasValidCookie());
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
	}
	
	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		self::$tplObj = TemplateEngine::getInstance();
		self::getTPL()->setLanguageID(self::getLanguage()->languageID);
		$this->assignDefaultTemplateVariables();
		
		$this->initStyle();
	}
	
	/**
	 * Initializes the user's style.
	 */
	protected function initStyle() {
		if (isset($_REQUEST['styleID'])) {
			self::getSession()->setStyleID(intval($_REQUEST['styleID']));
		}
		
		$styleHandler = StyleHandler::getInstance();
		$styleHandler->changeStyle(self::getSession()->getStyleID());
	}
	
	/**
	 * Executes the blacklist.
	 */
	protected function initBlacklist() {
		$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
		
		if (defined('BLACKLIST_IP_ADDRESSES') && BLACKLIST_IP_ADDRESSES != '') {
			if (!StringUtil::executeWordFilter(UserUtil::convertIPv6To4(self::getSession()->ipAddress), BLACKLIST_IP_ADDRESSES)) {
				if ($isAjax) {
					throw new AJAXException(self::getLanguage()->getDynamicVariable('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS);
				}
				else {
					throw new PermissionDeniedException();
				}
			}
			else if (!StringUtil::executeWordFilter(self::getSession()->ipAddress, BLACKLIST_IP_ADDRESSES)) {
				if ($isAjax) {
					throw new AJAXException(self::getLanguage()->getDynamicVariable('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS);
				}
				else {
					throw new PermissionDeniedException();
				}
			}
		}
		if (defined('BLACKLIST_USER_AGENTS') && BLACKLIST_USER_AGENTS != '') {
			if (!StringUtil::executeWordFilter(self::getSession()->userAgent, BLACKLIST_USER_AGENTS)) {
				if ($isAjax) {
					throw new AJAXException(self::getLanguage()->getDynamicVariable('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS);
				}
				else {
					throw new PermissionDeniedException();
				}
			}
		}
		if (defined('BLACKLIST_HOSTNAMES') && BLACKLIST_HOSTNAMES != '') {
			if (!StringUtil::executeWordFilter(@gethostbyaddr(self::getSession()->ipAddress), BLACKLIST_HOSTNAMES)) {
				if ($isAjax) {
					throw new AJAXException(self::getLanguage()->getDynamicVariable('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS);
				}
				else {
					throw new PermissionDeniedException();
				}
			}
		}
		
		// handle banned users
		if (self::getUser()->userID && self::getUser()->banned && !self::getUser()->hasOwnerAccess()) {
			if ($isAjax) {
				throw new AJAXException(self::getLanguage()->getDynamicVariable('wcf.user.error.isBanned'), AJAXException::INSUFFICIENT_PERMISSIONS);
			}
			else {
				self::$forceLogout = true;
				
				// remove cookies
				if (isset($_COOKIE[COOKIE_PREFIX.'userID'])) {
					HeaderUtil::setCookie('userID', 0);
				}
				if (isset($_COOKIE[COOKIE_PREFIX.'password'])) {
					HeaderUtil::setCookie('password', '');
				}
				
				throw new NamedUserException(self::getLanguage()->getDynamicVariable('wcf.user.error.isBanned'));
			}
		}
	}
	
	/**
	 * Initializes applications.
	 */
	protected function initApplications() {
		// step 1) load all applications
		$loadedApplications = [];
		
		// register WCF as application
		self::$applications['wcf'] = ApplicationHandler::getInstance()->getApplicationByID(1);
		
		if (!class_exists(WCFACP::class, false)) {
			static::getTPL()->assign('baseHref', self::$applications['wcf']->getPageURL());
		}
		
		// start main application
		$application = ApplicationHandler::getInstance()->getActiveApplication();
		if ($application->packageID != 1) {
			$loadedApplications[] = $this->loadApplication($application);
			
			// register primary application
			$abbreviation = ApplicationHandler::getInstance()->getAbbreviation($application->packageID);
			self::$applications[$abbreviation] = $application;
		}
		
		// start dependent applications
		$applications = ApplicationHandler::getInstance()->getDependentApplications();
		foreach ($applications as $application) {
			if ($application->packageID == 1) {
				// ignore WCF
				continue;
			}
			else if ($application->isTainted) {
				// ignore apps flagged for uninstallation
				continue;
			}
			
			$loadedApplications[] = $this->loadApplication($application, true);
		}
		
		// step 2) run each application
		if (!class_exists('wcf\system\WCFACP', false)) {
			/** @var IApplication $application */
			foreach ($loadedApplications as $application) {
				$application->__run();
			}
			
			// refresh the session 1 minute before it expires
			self::getTPL()->assign('__sessionKeepAlive', SESSION_TIMEOUT - 60);
		}
	}
	
	/**
	 * Loads an application.
	 * 
	 * @param	Application		$application
	 * @param	boolean			$isDependentApplication
	 * @return	IApplication
	 * @throws	SystemException
	 */
	protected function loadApplication(Application $application, $isDependentApplication = false) {
		$package = PackageCache::getInstance()->getPackage($application->packageID);
		// package cache might be outdated
		if ($package === null) {
			$package = new Package($application->packageID);
			
			// package cache is outdated, discard cache
			if ($package->packageID) {
				PackageEditor::resetCache();
			}
			else {
				// package id is invalid
				throw new SystemException("application identified by package id '".$application->packageID."' is unknown");
			}
		}
		
		$abbreviation = ApplicationHandler::getInstance()->getAbbreviation($application->packageID);
		$packageDir = FileUtil::getRealPath(WCF_DIR.$package->packageDir);
		self::$autoloadDirectories[$abbreviation] = $packageDir . 'lib/';
		
		$className = $abbreviation.'\system\\'.strtoupper($abbreviation).'Core';
		
		// class was not found, possibly the app was moved, but `packageDir` has not been adjusted
		if (!class_exists($className)) {
			// check if both the Core and the app are on the same domain
			$coreApp = ApplicationHandler::getInstance()->getApplicationByID(1);
			if ($coreApp->domainName === $application->domainName) {
				// resolve the relative path and use it to construct the autoload directory
				$relativePath = FileUtil::getRelativePath($coreApp->domainPath, $application->domainPath);
				if ($relativePath !== './') {
					$packageDir = FileUtil::getRealPath(WCF_DIR.$relativePath);
					self::$autoloadDirectories[$abbreviation] = $packageDir . 'lib/';
					
					if (class_exists($className)) {
						// the class can now be found, update the `packageDir` value
						(new PackageEditor($package))->update(['packageDir' => $relativePath]);
					}
				}
			}
		}
		
		if (class_exists($className) && is_subclass_of($className, IApplication::class)) {
			// include config file
			$configPath = $packageDir . PackageInstallationDispatcher::CONFIG_FILE;
			if (!file_exists($configPath)) {
				Package::writeConfigFile($package->packageID);
			}
			
			if (file_exists($configPath)) {
				require_once($configPath);
			}
			else {
				throw new SystemException('Unable to load configuration for '.$package->package);
			}
			
			// register template path if not within ACP
			if (!class_exists('wcf\system\WCFACP', false)) {
				// add template path and abbreviation
				static::getTPL()->addApplication($abbreviation, $packageDir . 'templates/');
			}
			EmailTemplateEngine::getInstance()->addApplication($abbreviation, $packageDir . 'templates/');
			
			// init application and assign it as template variable
			self::$applicationObjects[$application->packageID] = call_user_func([$className, 'getInstance']);
			static::getTPL()->assign('__'.$abbreviation, self::$applicationObjects[$application->packageID]);
			EmailTemplateEngine::getInstance()->assign('__'.$abbreviation, self::$applicationObjects[$application->packageID]);
		}
		else {
			unset(self::$autoloadDirectories[$abbreviation]);
			throw new SystemException("Unable to run '".$package->package."', '".$className."' is missing or does not implement '".IApplication::class."'.");
		}
		
		// register template path in ACP
		if (class_exists('wcf\system\WCFACP', false)) {
			static::getTPL()->addApplication($abbreviation, $packageDir . 'acp/templates/');
		}
		else if (!$isDependentApplication) {
			// assign base tag
			static::getTPL()->assign('baseHref', $application->getPageURL());
		}
		
		// register application
		self::$applications[$abbreviation] = $application;
		
		return self::$applicationObjects[$application->packageID];
	}
	
	/**
	 * Returns the corresponding application object. Does not support the 'wcf' pseudo application.
	 * 
	 * @param	Application	$application
	 * @return	IApplication
	 */
	public static function getApplicationObject(Application $application) {
		if (isset(self::$applicationObjects[$application->packageID])) {
			return self::$applicationObjects[$application->packageID];
		}
		
		return null;
	}
	
	/**
	 * Returns the invoked application.
	 * 
	 * @return      Application
	 * @since	3.1
	 */
	public static function getActiveApplication() {
		return ApplicationHandler::getInstance()->getActiveApplication();
	}
	
	/**
	 * Loads an application on runtime, do not use this outside the package installation.
	 * 
	 * @param	integer		$packageID
	 */
	public static function loadRuntimeApplication($packageID) {
		$package = new Package($packageID);
		$application = new Application($packageID);
		
		$abbreviation = Package::getAbbreviation($package->package);
		$packageDir = FileUtil::getRealPath(WCF_DIR.$package->packageDir);
		self::$autoloadDirectories[$abbreviation] = $packageDir . 'lib/';
		self::$applications[$abbreviation] = $application;
		self::getTPL()->addApplication($abbreviation, $packageDir . 'acp/templates/');
	}
	
	/**
	 * Initializes core object cache.
	 */
	protected function initCoreObjects() {
		// ignore core objects if installing WCF
		if (PACKAGE_ID == 0) {
			return;
		}
		
		self::$coreObjectCache = CoreObjectCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Assigns some default variables to the template engine.
	 */
	protected function assignDefaultTemplateVariables() {
		$wcf = $this;
		
		if (ENABLE_ENTERPRISE_MODE) {
			$wcf = new TemplateScriptingCore($wcf);
		}
		
		self::getTPL()->registerPrefilter(['event', 'hascontent', 'lang']);
		self::getTPL()->assign([
			'__wcf' => $wcf,
			'__wcfVersion' => LAST_UPDATE_TIME // @deprecated 2.1, use LAST_UPDATE_TIME directly
		]);
		
		$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
		// Execute background queue in this request, if it was requested and AJAX isn't used.
		if (!$isAjax) {
			if (self::getSession()->getVar('forceBackgroundQueuePerform')) {
				self::getTPL()->assign([
					'forceBackgroundQueuePerform' => true
				]);
				
				self::getSession()->unregister('forceBackgroundQueuePerform');
			}
		}
		
		EmailTemplateEngine::getInstance()->registerPrefilter(['event', 'hascontent', 'lang']);
		EmailTemplateEngine::getInstance()->assign([
			'__wcf' => $wcf
		]);
	}
	
	/**
	 * Wrapper for the getter methods of this class.
	 * 
	 * @param	string		$name
	 * @return	mixed		value
	 * @throws	SystemException
	 */
	public function __get($name) {
		$method = 'get'.ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		
		throw new SystemException("method '".$method."' does not exist in class WCF");
	}
	
	/**
	 * Returns true if current application (WCF) is treated as active and was invoked directly.
	 *
	 * @return	boolean
	 */
	public function isActiveApplication() {
		return (ApplicationHandler::getInstance()->getActiveApplication()->packageID == 1);
	}
	
	/**
	 * Changes the active language.
	 * 
	 * @param	integer		$languageID
	 */
	public static final function setLanguage($languageID) {
		if (!$languageID || LanguageFactory::getInstance()->getLanguage($languageID) === null) {
			$languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
		}
		
		self::$languageObj = LanguageFactory::getInstance()->getLanguage($languageID);
		
		// the template engine may not be available yet, usually happens when
		// changing the user (and thus the language id) during session init
		if (self::$tplObj !== null) {
			self::getTPL()->setLanguageID(self::getLanguage()->languageID);
			EmailTemplateEngine::getInstance()->setLanguageID(self::getLanguage()->languageID);
		}
	}
	
	/**
	 * Includes the required util or exception classes automatically.
	 * 
	 * @param	string		$className
	 * @see		spl_autoload_register()
	 */
	public static final function autoload($className) {
		$namespaces = explode('\\', $className);
		if (count($namespaces) > 1) {
			$applicationPrefix = array_shift($namespaces);
			if ($applicationPrefix === '') {
				$applicationPrefix = array_shift($namespaces);
			}
			if (isset(self::$autoloadDirectories[$applicationPrefix])) {
				$classPath = self::$autoloadDirectories[$applicationPrefix] . implode('/', $namespaces) . '.class.php';
				
				// PHP will implicitly check if the file exists when including it, which means that we can save a
				// redundant syscall/fs access by not checking for existence ourselves. Do not use require_once()!
				@include_once($classPath);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public final function __call($name, array $arguments) {
		// bug fix to avoid php crash, see http://bugs.php.net/bug.php?id=55020
		if (!method_exists($this, $name)) {
			return self::__callStatic($name, $arguments);
		}
		
		throw new \BadMethodCallException("Call to undefined method WCF::{$name}().");
	}
	
	/**
	 * Returns dynamically loaded core objects.
	 * 
	 * @param	string		$name
	 * @param	array		$arguments
	 * @return	object
	 * @throws	SystemException
	 */
	public static final function __callStatic($name, array $arguments) {
		$className = preg_replace('~^get~', '', $name);
		
		if (isset(self::$coreObject[$className])) {
			return self::$coreObject[$className];
		}
		
		$objectName = self::getCoreObject($className);
		if ($objectName === null) {
			throw new SystemException("Core object '".$className."' is unknown.");
		}
		
		if (class_exists($objectName)) {
			if (!is_subclass_of($objectName, SingletonFactory::class)) {
				throw new ParentClassException($objectName, SingletonFactory::class);
			}
			
			self::$coreObject[$className] = call_user_func([$objectName, 'getInstance']);
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
		if (isset(self::$coreObjectCache[$className])) {
			return self::$coreObjectCache[$className];
		}
		
		return null;
	}
	
	/**
	 * Returns true if the debug mode is enabled, otherwise false.
	 * 
	 * @param	boolean		$ignoreACP
	 * @return	boolean
	 */
	public static function debugModeIsEnabled($ignoreACP = false) {
		// ACP override
		if (!$ignoreACP && self::$overrideDebugMode) {
			return true;
		}
		else if (defined('ENABLE_DEBUG_MODE') && ENABLE_DEBUG_MODE) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if benchmarking is enabled, otherwise false.
	 * 
	 * @return	boolean
	 */
	public static function benchmarkIsEnabled() {
		// benchmarking is enabled by default
		if (!defined('ENABLE_BENCHMARK') || ENABLE_BENCHMARK) return true;
		return false;
	}
	
	/**
	 * Returns domain path for given application.
	 * 
	 * @param	string		$abbreviation
	 * @return	string
	 */
	public static function getPath($abbreviation = 'wcf') {
		// workaround during WCFSetup
		if (!PACKAGE_ID) {
			return '../';
		}
		
		if (!isset(self::$applications[$abbreviation])) {
			$abbreviation = 'wcf';
		}
		
		return self::$applications[$abbreviation]->getPageURL();
	}
	
	/**
	 * Returns the domain path for the currently active application,
	 * used to avoid CORS requests.
	 * 
	 * @return      string
	 */
	public static function getActivePath() {
		if (!PACKAGE_ID) {
			return self::getPath();
		}
		
		// We cannot rely on the ApplicationHandler's `getActiveApplication()` because
		// it uses the requested controller to determine the namespace. However, starting
		// with WoltLab Suite 5.2, system pages can be virtually assigned to a different
		// app, resolving against the target app without changing the namespace.
		return self::getPath(ApplicationHandler::getInstance()->getAbbreviation(PACKAGE_ID));
	}
	
	/**
	 * Returns a fully qualified anchor for current page.
	 * 
	 * @param	string		$fragment
	 * @return	string
	 */
	public function getAnchor($fragment) {
		return StringUtil::encodeHTML(self::getRequestURI() . '#' . $fragment);
	}
	
	/**
	 * Returns the currently active page or null if unknown.
	 * 
	 * @return	Page|null
	 */
	public static function getActivePage() {
		if (self::getActiveRequest() === null) {
			return null;
		}
		
		if (self::getActiveRequest()->getClassName() === CmsPage::class) {
			$metaData = self::getActiveRequest()->getMetaData();
			if (isset($metaData['cms'])) {
				return PageCache::getInstance()->getPage($metaData['cms']['pageID']);
			}
			
			return null;
		}
		
		return PageCache::getInstance()->getPageByController(self::getActiveRequest()->getClassName());
	}
	
	/**
	 * Returns the currently active request.
	 * 
	 * @return Request
	 */
	public static function getActiveRequest() {
		return RequestHandler::getInstance()->getActiveRequest();
	}
	
	/**
	 * Returns the URI of the current page.
	 * 
	 * @return	string
	 */
	public static function getRequestURI() {
		return preg_replace('~^(https?://[^/]+)(?:/.*)?$~', '$1', self::getTPL()->get('baseHref')) . $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Resets Zend Opcache cache if installed and enabled.
	 * 
	 * @param	string		$script
	 */
	public static function resetZendOpcache($script = '') {
		if (self::$zendOpcacheEnabled === null) {
			self::$zendOpcacheEnabled = false;
			
			if (extension_loaded('Zend Opcache') && @ini_get('opcache.enable')) {
				self::$zendOpcacheEnabled = true;
			}
			
		}
		
		if (self::$zendOpcacheEnabled) {
			if (empty($script)) {
				\opcache_reset();
			}
			else {
				\opcache_invalidate($script, true);
			}
		}
	}
	
	/**
	 * Returns style handler.
	 * 
	 * @return	StyleHandler
	 */
	public function getStyleHandler() {
		return StyleHandler::getInstance();
	}
	
	/**
	 * Returns box handler.
	 *
	 * @return	BoxHandler
	 * @since	3.0
	 */
	public function getBoxHandler() {
		return BoxHandler::getInstance();
	}
	
	/**
	 * Returns number of available updates.
	 * 
	 * @return	integer
	 */
	public function getAvailableUpdates() {
		$data = PackageUpdateCacheBuilder::getInstance()->getData();
		return $data['updates'];
	}
	
	/**
	 * Returns a 8 character prefix for editor autosave.
	 * 
	 * @return	string
	 */
	public function getAutosavePrefix() {
		return substr(sha1(preg_replace('~^https~', 'http', self::getPath())), 0, 8);
	}
	
	/**
	 * Returns the favicon URL or a base64 encoded image.
	 * 
	 * @return	string
	 */
	public function getFavicon() {
		$activeApplication = ApplicationHandler::getInstance()->getActiveApplication();
		$wcf = ApplicationHandler::getInstance()->getWCF();
		$favicon = StyleHandler::getInstance()->getStyle()->getRelativeFavicon();
		
		if ($activeApplication->domainName !== $wcf->domainName) {
			if (file_exists(WCF_DIR.$favicon)) {
				$favicon = file_get_contents(WCF_DIR.$favicon);
				
				return 'data:image/x-icon;base64,' . base64_encode($favicon);
			}
		}
		
		return self::getPath() . $favicon;
	}
	
	/**
	 * Returns true if the desktop notifications should be enabled.
	 * 
	 * @return      boolean
	 */
	public function useDesktopNotifications() {
		if (!ENABLE_DESKTOP_NOTIFICATIONS) {
			return false;
		}
		else if (ApplicationHandler::getInstance()->isMultiDomainSetup()) {
			$application = ApplicationHandler::getInstance()->getApplicationByID(DESKTOP_NOTIFICATION_PACKAGE_ID);
			// mismatch, default to Core
			if ($application === null) $application = ApplicationHandler::getInstance()->getApplicationByID(1);
			
			$currentApplication = ApplicationHandler::getInstance()->getActiveApplication();
			if ($currentApplication->domainName != $application->domainName) {
				// different domain
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Returns true if currently active request represents the landing page.
	 * 
	 * @return	boolean
	 */
	public static function isLandingPage() {
		if (self::getActiveRequest() === null) {
			return false;
		}
		
		return self::getActiveRequest()->isLandingPage();
	}
	
	/**
	 * Returns true if the given API version is currently supported.
	 * 
	 * @param       integer         $apiVersion
	 * @return      boolean
	 * @deprecated 5.2
	 */
	public static function isSupportedApiVersion($apiVersion) {
		return ($apiVersion == WSC_API_VERSION) || in_array($apiVersion, self::$supportedLegacyApiVersions);
	}
	
	/**
	 * Returns the list of supported legacy API versions.
	 * 
	 * @return      integer[]
	 * @deprecated 5.2
	 */
	public static function getSupportedLegacyApiVersions() {
		return self::$supportedLegacyApiVersions;
	}
	
	/**
	 * Initialises the cronjobs.
	 */
	protected function initCronjobs() {
		if (PACKAGE_ID) {
			self::getTPL()->assign('executeCronjobs', CronjobScheduler::getInstance()->getNextExec() < TIME_NOW && defined('OFFLINE') && !OFFLINE);
		}
	}
	
	/**
	 * Checks recursively that the most important system files of `com.woltlab.wcf` are writable.
	 * 
	 * @throws	\RuntimeException	if any relevant file or directory is not writable
	 */
	public static function checkWritability() {
		$nonWritablePaths = [];
		
		$nonRecursiveDirectories = [
			'',
			'acp/',
			'tmp/'
		];
		foreach ($nonRecursiveDirectories as $directory) {
			$path = WCF_DIR . $directory;
			if ($path === 'tmp/' && !is_dir($path)) {
				continue;
			}
			
			if (!is_writable($path)) {
				$nonWritablePaths[] = FileUtil::getRelativePath($_SERVER['DOCUMENT_ROOT'], $path);
				continue;
			}
			
			DirectoryUtil::getInstance($path, false)->executeCallback(function($file, \SplFileInfo $fileInfo) use ($path, &$nonWritablePaths) {
				if ($fileInfo instanceof \DirectoryIterator) {
					if (!is_writable($fileInfo->getPath())) {
						$nonWritablePaths[] = FileUtil::getRelativePath($_SERVER['DOCUMENT_ROOT'], $fileInfo->getPath());
					}
				}
				else if (!is_writable($fileInfo->getRealPath())) {
					$nonWritablePaths[] = FileUtil::getRelativePath($_SERVER['DOCUMENT_ROOT'], $fileInfo->getPath()) . $fileInfo->getFilename();
				}
			});
		}
		
		$recursiveDirectories = [
			'acp/js/',
			'acp/style/',
			'acp/templates/',
			'acp/uninstall/',
			'js/',
			'lib/',
			'log/',
			'style/',
			'templates/'
		];
		foreach ($recursiveDirectories as $directory) {
			$path = WCF_DIR . $directory;
			
			if (!is_writable($path)) {
				$nonWritablePaths[] = FileUtil::getRelativePath($_SERVER['DOCUMENT_ROOT'], $path);
				continue;
			}
			
			DirectoryUtil::getInstance($path)->executeCallback(function($file, \SplFileInfo $fileInfo) use ($path, &$nonWritablePaths) {
				if ($fileInfo instanceof \DirectoryIterator) {
					if (!is_writable($fileInfo->getPath())) {
						$nonWritablePaths[] = FileUtil::getRelativePath($_SERVER['DOCUMENT_ROOT'], $fileInfo->getPath());
					}
				}
				else if (!is_writable($fileInfo->getRealPath())) {
					$nonWritablePaths[] = FileUtil::getRelativePath($_SERVER['DOCUMENT_ROOT'], $fileInfo->getPath()) . $fileInfo->getFilename();
				}
			});
		}
		
		if (!empty($nonWritablePaths)) {
			$maxPaths = 10;
			throw new \RuntimeException('The following paths are not writable: ' . implode(',', array_slice($nonWritablePaths, 0, $maxPaths)) . (count($nonWritablePaths) > $maxPaths ? ',' . StringUtil::HELLIP : ''));
		}
	}
}
