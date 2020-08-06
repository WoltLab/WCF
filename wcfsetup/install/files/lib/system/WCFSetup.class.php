<?php
namespace wcf\system;
use wcf\data\language\LanguageEditor;
use wcf\data\language\SetupLanguage;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\cache\builder\LanguageCacheBuilder;
use wcf\system\database\exception\DatabaseException;
use wcf\system\database\util\SQLParser;
use wcf\system\database\MySQLDatabase;
use wcf\system\devtools\DevtoolsSetup;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\image\adapter\GDImageAdapter;
use wcf\system\image\adapter\ImagickImageAdapter;
use wcf\system\io\File;
use wcf\system\io\Tar;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageArchive;
use wcf\system\request\RouteHandler;
use wcf\system\session\ACPSessionFactory;
use wcf\system\session\SessionHandler;
use wcf\system\setup\Installer;
use wcf\system\setup\SetupFileHandler;
use wcf\system\template\SetupTemplateEngine;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;
use wcf\util\XML;

// define
define('PACKAGE_ID', 0);
define('HTTP_ENABLE_GZIP', 0);
define('HTTP_GZIP_LEVEL', 0);
define('HTTP_SEND_X_FRAME_OPTIONS', 0);
define('CACHE_SOURCE_TYPE', 'disk');
define('MODULE_MASTER_PASSWORD', 1);
define('ENABLE_DEBUG_MODE', 1);
define('ENABLE_BENCHMARK', 0);
define('ENABLE_ENTERPRISE_MODE', 0);

/**
 * Executes the installation of the basic WCF systems.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System
 */
class WCFSetup extends WCF {
	/**
	 * list of available languages
	 * @var	string[]
	 */
	protected static $availableLanguages = [];
	
	/**
	 * installation directories
	 * @var	string[]
	 */
	protected static $directories = [];
	
	/**
	 * language code of selected installation language
	 * @var	string
	 */
	protected static $selectedLanguageCode = 'en';
	
	/**
	 * selected languages to be installed
	 * @var	string[]
	 */
	protected static $selectedLanguages = [];
	
	/**
	 * list of installed files
	 * @var	string[]
	 */
	protected static $installedFiles = [];
	
	/**
	 * indicates if developer mode is used to install
	 * @var	boolean
	 */
	protected static $developerMode = 0;
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * Calls all init functions of the WCFSetup class and starts the setup process.
	 */
	public function __construct() {
		@set_time_limit(0);
		
		static::getDeveloperMode();
		static::getLanguageSelection();
		static::getInstallationDirectories();
		$this->initLanguage();
		$this->initTPL();
		/** @noinspection PhpUndefinedMethodInspection */
		self::getLanguage()->loadLanguage();
		static::getPackageNames();
		
		// start setup
		$this->setup();
	}
	
	/**
	 * Sets the status of the developer mode.
	 */
	protected static function getDeveloperMode() {
		if (isset($_GET['dev'])) self::$developerMode = intval($_GET['dev']);
		else if (isset($_POST['dev'])) self::$developerMode = intval($_POST['dev']);
	}
	
	/**
	 * Sets the selected language.
	 */
	protected static function getLanguageSelection() {
		self::$availableLanguages = self::getAvailableLanguages();
		
		if (isset($_REQUEST['languageCode']) && isset(self::$availableLanguages[$_REQUEST['languageCode']])) {
			self::$selectedLanguageCode = $_REQUEST['languageCode'];
		}
		else {
			self::$selectedLanguageCode = LanguageFactory::getPreferredLanguage(array_keys(self::$availableLanguages), self::$selectedLanguageCode);
		}
		
		if (isset($_POST['selectedLanguages']) && is_array($_POST['selectedLanguages'])) {
			self::$selectedLanguages = $_POST['selectedLanguages'];
		}
	}
	
	/**
	 * Sets the selected wcf dir from request.
	 * 
	 * @since	3.0
	 */
	protected static function getInstallationDirectories() {
		if (!empty($_REQUEST['directories']) && is_array($_REQUEST['directories'])) {
			foreach ($_REQUEST['directories'] as $application => $directory) {
				self::$directories[$application] = $directory;
				
				if ($application === 'wcf' && @file_exists(self::$directories['wcf'])) {
					define('RELATIVE_WCF_DIR', FileUtil::getRelativePath(INSTALL_SCRIPT_DIR, self::$directories['wcf']));
				}
			}
		}
		
		define('WCF_DIR', (isset(self::$directories['wcf']) ? self::$directories['wcf'] : ''));
	}
	
	/**
	 * Initialises the language engine.
	 */
	protected function initLanguage() {
		// set mb settings
		mb_internal_encoding('UTF-8');
		if (function_exists('mb_regex_encoding')) mb_regex_encoding('UTF-8');
		mb_language('uni');
		
		// init setup language
		self::$languageObj = new SetupLanguage(null, ['languageCode' => self::$selectedLanguageCode]);
	}
	
	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		self::$tplObj = SetupTemplateEngine::getInstance();
		self::getTPL()->setLanguageID((self::$selectedLanguageCode == 'en' ? 0 : 1));
		self::getTPL()->setCompileDir(TMP_DIR);
		self::getTPL()->addApplication('wcf', TMP_DIR);
		self::getTPL()->registerPrefilter(['lang']);
		self::getTPL()->assign([
			'__wcf' => $this,
			'tmpFilePrefix' => TMP_FILE_PREFIX,
			'languageCode' => self::$selectedLanguageCode,
			'selectedLanguages' => self::$selectedLanguages,
			'directories' => self::$directories,
			'developerMode' => self::$developerMode
		]);
	}
	
	/**
	 * Returns all languages from WCFSetup.tar.gz.
	 * 
	 * @return	string[]
	 */
	protected static function getAvailableLanguages() {
		$languages = $match = [];
		foreach (glob(TMP_DIR.'setup/lang/*.xml') as $file) {
			$xml = new XML();
			$xml->load($file);
			$languageCode = LanguageEditor::readLanguageCodeFromXML($xml);
			$languageName = LanguageEditor::readLanguageNameFromXML($xml);
			
			$languages[$languageCode] = $languageName;
		}
		
		// sort languages by language name
		asort($languages);
		
		return $languages;
	}
	
	/**
	 * Calculates the current state of the progress bar.
	 * 
	 * @param	integer		$currentStep
	 */
	protected function calcProgress($currentStep) {
		// calculate progress
		$progress = round((100 / 25) * ++$currentStep, 0);
		self::getTPL()->assign(['progress' => $progress]);
	}
	
	/**
	 * Executes the setup steps.
	 */
	protected function setup() {
		// get current step
		if (isset($_REQUEST['step'])) $step = $_REQUEST['step'];
		else $step = 'selectSetupLanguage';
		
		header('set-cookie: wcfsetup_cookietest='.TMP_FILE_PREFIX.'; domain=' . str_replace(RouteHandler::getProtocol(), '', RouteHandler::getHost()) . (RouteHandler::secureConnection() ? '; secure' : ''));
		
		// execute current step
		switch ($step) {
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'selectSetupLanguage':
				if (!self::$developerMode) {
					$this->calcProgress(0);
					$this->selectSetupLanguage();
					break;
				}
			
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'showLicense':
				if (!self::$developerMode) {
					$this->calcProgress(1);
					$this->showLicense();
					break;
				}
			
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'showSystemRequirements':
				if (!self::$developerMode) {
					$this->calcProgress(2);
					$this->showSystemRequirements();
					break;
				}
			
			case 'configureDirectories':
				$this->calcProgress(3);
				$this->configureDirectories();
			break;
			
			case 'unzipFiles':
				$this->calcProgress(4);
				$this->unzipFiles();
			break;
			
			case 'selectLanguages':
				$this->calcProgress(5);
				$this->selectLanguages();
			break;
			
			case 'configureDB':
				$this->calcProgress(6);
				$this->configureDB();
			break;
			
			case 'createDB':
				$currentStep = 7;
				if (isset($_POST['offset'])) {
					$currentStep += intval($_POST['offset']);
				}
				
				$this->calcProgress($currentStep);
				$this->createDB();
			break;
			
			case 'logFiles':
				$this->calcProgress(21);
				$this->logFiles();
			break;
			
			case 'installLanguage':
				$this->calcProgress(22);
				$this->installLanguage();
			break;
			
			case 'createUser':
				$this->calcProgress(23);
				$this->createUser();
			break;
			
			case 'installPackages':
				$this->calcProgress(24);
				$this->installPackages();
			break;
		}
	}
	
	/**
	 * Shows the first setup page.
	 */
	protected function selectSetupLanguage() {
		WCF::getTPL()->assign([
			'availableLanguages' => self::$availableLanguages,
			'nextStep' => 'showLicense'
		]);
		WCF::getTPL()->display('stepSelectSetupLanguage');
	}
	
	/**
	 * Shows the license agreement.
	 */
	protected function showLicense() {
		if (isset($_POST['send'])) {
			if (isset($_POST['accepted'])) {
				$this->gotoNextStep('showSystemRequirements');
				exit;
			}
			else {
				WCF::getTPL()->assign(['missingAcception' => true]);
			}
		
		}
		
		if (file_exists(TMP_DIR.'setup/license/license_'.self::$selectedLanguageCode.'.txt')) {
			$license = file_get_contents(TMP_DIR.'setup/license/license_'.self::$selectedLanguageCode.'.txt');
		}
		else {
			$license = file_get_contents(TMP_DIR.'setup/license/license_en.txt');
		}
		
		WCF::getTPL()->assign([
			'license' => $license,
			'nextStep' => 'showLicense'
		]);
		WCF::getTPL()->display('stepShowLicense');
	}
	
	/**
	 * Shows the system requirements.
	 */
	protected function showSystemRequirements() {
		$system = [];
		
		// php version
		$system['phpVersion']['value'] = phpversion();
		$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $system['phpVersion']['value']);
		$system['phpVersion']['result'] = (version_compare($comparePhpVersion, '7.0.22') >= 0);
		
		// sql
		$system['sql']['result'] = MySQLDatabase::isSupported();
		
		// upload_max_filesize
		$system['uploadMaxFilesize']['value'] = min(ini_get('upload_max_filesize'), ini_get('post_max_size'));
		$system['uploadMaxFilesize']['result'] = (intval($system['uploadMaxFilesize']['value']) > 0);
		
		// graphics library
		$system['graphicsLibrary']['result'] = false;
		$system['graphicsLibrary']['value'] = '';
		if (ImagickImageAdapter::isSupported() && ImagickImageAdapter::supportsAnimatedGIFs(ImagickImageAdapter::getVersion())) {
			$system['graphicsLibrary'] = [
				'result' => true,
				'value' => 'ImageMagick',
			];
		}
		else if (GDImageAdapter::isSupported()) {
			$system['graphicsLibrary'] = [
				'result' => true,
				'value' => 'GD Library',
			];
		}
		
		// memory limit
		$system['memoryLimit']['value'] = ini_get('memory_limit');
		$system['memoryLimit']['result'] = $this->compareMemoryLimit();
		
		// openssl extension
		$system['openssl']['result'] = @extension_loaded('openssl');
		
		// misconfigured reverse proxy / cookies
		$system['hostname']['result'] = true;
		list($system['hostname']['value']) = explode(':', $_SERVER['HTTP_HOST'], 2);
		if (!empty($_SERVER['HTTP_REFERER'])) {
			$refererHostname = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
			$system['hostname']['result'] = $_SERVER['HTTP_HOST'] == $refererHostname;
		}
		
		$system['cookie']['result'] = !empty($_COOKIE['wcfsetup_cookietest']) && $_COOKIE['wcfsetup_cookietest'] == TMP_FILE_PREFIX;
		
		WCF::getTPL()->assign([
			'system' => $system,
			'nextStep' => 'configureDirectories'
		]);
		WCF::getTPL()->display('stepShowSystemRequirements');
	}
	
	/**
	 * Returns true if memory_limit is set to at least 128 MB
	 * 
	 * @return	boolean
	 */
	protected function compareMemoryLimit() {
		$memoryLimit = ini_get('memory_limit');
		
		// no limit
		if ($memoryLimit == -1) {
			return true;
		}
		
		// completely numeric, PHP assumes byte
		if (is_numeric($memoryLimit)) {
			$memoryLimit = $memoryLimit / 1024 / 1024;
			return ($memoryLimit >= 128);
		}
		
		// PHP supports 'K', 'M' and 'G' shorthand notation
		if (preg_match('~^(\d+)([KMG])$~', $memoryLimit, $matches)) {
			switch ($matches[2]) {
				case 'K':
					$memoryLimit = $matches[1] * 1024;
					return ($memoryLimit >= 128);
				break;
				
				case 'M':
					return ($matches[1] >= 128);
				break;
				
				case 'G':
					return ($matches[1] >= 1);
				break;
			}
		}
		
		return false;
	}
	
	/**
	 * Searches the wcf dir.
	 * 
	 * @since	3.0
	 */
	protected function configureDirectories() {
		// get available packages
		$packages = [];
		foreach (glob(TMP_DIR . 'install/packages/*') as $file) {
			$filename = basename($file);
			if (preg_match('~\.(?:tar|tar\.gz|tgz)$~', $filename)) {
				$package = new PackageArchive($file);
				$package->openArchive();
				
				$application = Package::getAbbreviation($package->getPackageInfo('name'));
				
				$packages[$application] = [
					'directory' => $package->getPackageInfo('applicationDirectory') ?: $application,
					'packageDescription' => $package->getLocalizedPackageInfo('packageDescription'),
					'packageName' => $package->getLocalizedPackageInfo('packageName')
				];
			}
		}
		
		uasort($packages, function($a, $b) {
			return strcmp($a['packageName'], $b['packageName']);
		});
		
		// force cms being shown first
		$showOrder = ['wcf'];
		foreach (array_keys($packages) as $application) {
			if ($application !== 'wcf') $showOrder[] = $application;
		}
		
		$documentRoot = FileUtil::unifyDirSeparator(realpath($_SERVER['DOCUMENT_ROOT']));
		if (self::$developerMode && (isset($_ENV['WCFSETUP_USEDEFAULTWCFDIR']) || DevtoolsSetup::getInstance()->useDefaultInstallPath())) {
			// resolve path relative to document root
			$relativePath = FileUtil::getRelativePath($documentRoot, INSTALL_SCRIPT_DIR);
			foreach ($packages as $application => $packageData) {
				self::$directories[$application] = $relativePath . ($application === 'wcf' ? '' : $packageData['directory'] . '/');
			}
		}
		
		$errors = [];
		if (!empty(self::$directories)) {
			$applicationPaths = $knownPaths = [];
			
			// use $showOrder to ensure that the error message for duplicate directories
			// will trigger in display order rather than the random sort order returned
			// by glob() above
			foreach ($showOrder as $application) {
				$path = FileUtil::getRealPath($documentRoot . '/' . FileUtil::addTrailingSlash(FileUtil::removeLeadingSlash(self::$directories[$application])));
				if (!empty($documentRoot) && strpos($path, $documentRoot) !== 0) {
					// verify that given path is still within the current document root
					$errors[$application] = 'outsideDocumentRoot';
				}
				else if (in_array($path, $knownPaths)) {
					// prevent the same path for two or more applications
					$errors[$application] = 'duplicate';
				}
				else if (@is_file($path . 'global.php')) {
					// check if directory is empty (dotfiles are okay)
					$errors[$application] = 'notEmpty';
				}
				else {
					// try to create directory if it does not exist
					if (!is_dir($path) && !FileUtil::makePath($path)) {
						$errors[$application] = 'makePath';
					}
					
					try {
						FileUtil::makeWritable($path);
					}
					catch (SystemException $e) {
						$errors[$application] = 'makeWritable';
					}
				}
				
				$applicationPaths[$application] = $path;
				$knownPaths[] = $path;
			}
			
			if (empty($errors)) {
				// copy over the actual paths
				self::$directories = array_merge(self::$directories, $applicationPaths);
				WCF::getTPL()->assign(['directories' => self::$directories]);
				
				$this->unzipFiles();
				return;
			}
		}
		else {
			// resolve path relative to document root
			$relativePath = FileUtil::getRelativePath($documentRoot, INSTALL_SCRIPT_DIR);
			foreach ($packages as $application => $packageData) {
				$dir = $relativePath . ($application === 'wcf' ? '' : $packageData['directory'] . '/');
				if (mb_strpos($dir, './') === 0) $dir = mb_substr($dir, 1);
				
				self::$directories[$application] = $dir;
			}
		}
		
		WCF::getTPL()->assign([
			'directories' => self::$directories,
			'documentRoot' => $documentRoot,
			'errors' => $errors,
			'installScriptDir' => FileUtil::unifyDirSeparator(INSTALL_SCRIPT_DIR),
			'nextStep' => 'configureDirectories', // call this step again to validate paths
			'packages' => $packages,
			'showOrder' => $showOrder
		]);
		
		WCF::getTPL()->display('stepConfigureDirectories');
	}
	
	/**
	 * Unzips the files of the wcfsetup tar archive.
	 */
	protected function unzipFiles() {
		// WCF seems to be installed, abort
		if (@is_file(self::$directories['wcf'].'lib/system/WCF.class.php')) {
			throw new SystemException('Target directory seems to be an existing installation of WCF, unable to continue.');
		}
		// WCF not yet installed, install files first
		else {
			static::installFiles();
			
			$this->gotoNextStep('selectLanguages');
		}
	}
	
	/**
	 * Shows the page for choosing the installed languages.
	 */
	protected function selectLanguages() {
		$errorField = $errorType = '';
		
		// skip step in developer mode
		// select all available languages automatically
		if (self::$developerMode) {
			self::$selectedLanguages = [];
			foreach (self::$availableLanguages as $languageCode => $language) {
				self::$selectedLanguages[] = $languageCode;
			}
			
			self::getTPL()->assign(['selectedLanguages' => self::$selectedLanguages]);
			$this->gotoNextStep('configureDB');
			exit;
		}
		
		// start error handling
		if (isset($_POST['send'])) {
			try {
				// no languages selected
				if (empty(self::$selectedLanguages)) {
					throw new UserInputException('selectedLanguages');
				}
				
				// illegal selection
				foreach (self::$selectedLanguages as $language) {
					if (!isset(self::$availableLanguages[$language])) {
						throw new UserInputException('selectedLanguages');
					}
				}
				
				// no errors
				// go to next step
				$this->gotoNextStep('configureDB');
				exit;
			}
			catch (UserInputException $e) {
				$errorField = $e->getField();
				$errorType = $e->getType();
			}
		}
		else {
			self::$selectedLanguages[] = self::$selectedLanguageCode;
			WCF::getTPL()->assign(['selectedLanguages' => self::$selectedLanguages]);
		}
		
		WCF::getTPL()->assign([
			'errorField' => $errorField,
			'errorType' => $errorType,
			'availableLanguages' => self::$availableLanguages,
			'nextStep' => 'selectLanguages'
		]);
		WCF::getTPL()->display('stepSelectLanguages');
	}
	
	/**
	 * Shows the page for configuring the database connection.
	 */
	protected function configureDB() {
		$attemptConnection = isset($_POST['send']);
		
		if (self::$developerMode && isset($_ENV['WCFSETUP_DBHOST'])) {
			$dbHost = $_ENV['WCFSETUP_DBHOST'];
			$dbUser = $_ENV['WCFSETUP_DBUSER'];
			$dbPassword = $_ENV['WCFSETUP_DBPASSWORD'];
			$dbName = $_ENV['WCFSETUP_DBNAME'];
			$dbNumber = 1;
			
			$attemptConnection = true;
		}
		else if (self::$developerMode && ($config = DevtoolsSetup::getInstance()->getDatabaseConfig()) !== null) {
			$dbHost = $config['host'];
			$dbUser = $config['username'];
			$dbPassword = $config['password'];
			$dbName = $config['dbName'];
			$dbNumber = $config['dbNumber'];
			
			if ($config['auto']) $attemptConnection = true;
		}
		else {
			$dbHost = 'localhost';
			$dbUser = 'root';
			$dbPassword = '';
			$dbName = 'wcf';
			$dbNumber = 1;
		}
		
		if ($attemptConnection) {
			if (isset($_POST['dbHost'])) $dbHost = $_POST['dbHost'];
			if (isset($_POST['dbUser'])) $dbUser = $_POST['dbUser'];
			if (isset($_POST['dbPassword'])) $dbPassword = $_POST['dbPassword'];
			if (isset($_POST['dbName'])) $dbName = $_POST['dbName'];
			
			// ensure that $dbNumber is zero or a positive integer
			if (isset($_POST['dbNumber'])) $dbNumber = max(0, intval($_POST['dbNumber']));
			
			// get port
			$dbHostWithoutPort = $dbHost;
			$dbPort = 0;
			if (preg_match('/^(.+?):(\d+)$/', $dbHost, $match)) {
				$dbHostWithoutPort = $match[1];
				$dbPort = intval($match[2]);
			}
			
			// test connection
			try {
				// check connection data
				/** @var \wcf\system\database\Database $db */
				try {
					$db = new MySQLDatabase($dbHostWithoutPort, $dbUser, $dbPassword, $dbName, $dbPort, true, !!(self::$developerMode));
				}
				catch (DatabaseException $e) {
					switch ($e->getPrevious()->getCode()) {
						// try to manually create non-existing database
						case 1049:
							try {
								$db = new MySQLDatabase($dbHostWithoutPort, $dbUser, $dbPassword, $dbName, $dbPort, true, true);
							}
							catch (DatabaseException $e) {
								throw new SystemException("Unknown database '{$dbName}'. Please create the database manually.");
							}
							
							break;
						
						// work-around for older MySQL versions that don't know utf8mb4
						case 1115:
							throw new SystemException("Insufficient MySQL version. Version '5.5.35' or greater is needed.");
							break;
							
						default:
							throw $e;
					}
				}
				
				// check sql version
				$sqlVersion = $db->getVersion();
				$compareSQLVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $sqlVersion);
				if (stripos($sqlVersion, 'MariaDB')) {
					// MariaDB 5.5.47+ or 10.0.22+ are required
					// https://jira.mariadb.org/browse/MDEV-8756
					if ($compareSQLVersion[0] === '5') {
						// MariaDB 5.5.47+
						if (!(version_compare($compareSQLVersion, '5.5.47') >= 0)) {
							throw new SystemException("Insufficient MariaDB version '".$compareSQLVersion."'. Version '5.5.47' or greater, or version '10.0.22' or greater is needed.");
						}
					}
					else if (!(version_compare($compareSQLVersion, '10.0.22') >= 0)) {
						// MariaDB 10.0.22+
						throw new SystemException("Insufficient MariaDB version '".$compareSQLVersion."'. Version '5.5.47' or greater, or version '10.0.22' or greater is needed.");
					}
				}
				else {
					// MySQL 5.5.35+ or MySQL 8.0.14+ are required
					// https://bugs.mysql.com/bug.php?id=88718
					if ($compareSQLVersion[0] === '8') {
						// MySQL 8.0.14+
						if (!(version_compare($compareSQLVersion, '8.0.14') >= 0)) {
							throw new SystemException("Insufficient MySQL version '".$compareSQLVersion."'. Version '5.5.35' or greater, or version '8.0.14' or greater is needed.");
						}
					}
					else if (!(version_compare($compareSQLVersion, '5.5.35') >= 0)) {
						// MySQL 5.5.35+
						throw new SystemException("Insufficient MySQL version '".$compareSQLVersion."'. Version '5.5.35' or greater, or version '8.0.14' or greater is needed.");
					}
				}
				
				// check innodb support
				$sql = "SHOW ENGINES";
				$statement = $db->prepareStatement($sql);
				$statement->execute();
				$hasInnoDB = false;
				while ($row = $statement->fetchArray()) {
					if ($row['Engine'] == 'InnoDB' && in_array($row['Support'], ['DEFAULT', 'YES'])) {
						$hasInnoDB = true;
						break;
					}
				}
				
				if (!$hasInnoDB) {
					throw new SystemException("Support for InnoDB is missing.");
				}
				
				// check for PHP's MySQL native driver
				/*
				$sql = "SELECT 1";
				$statement = $db->prepareStatement($sql);
				$statement->execute();
				// MySQL native driver understands data types, libmysqlclient does not
				if ($statement->fetchSingleColumn() !== 1) {
					throw new SystemException("MySQLnd is not being used for database communication.");
				}
				*/
				
				// check for table conflicts
				$conflictedTables = $this->getConflictedTables($db, $dbNumber);
				
				// write config.inc
				if (empty($conflictedTables)) {
					// connection successfully established
					// write configuration to config.inc.php
					$file = new File(WCF_DIR.'config.inc.php');
					$file->write("<?php\n");
					$file->write("\$dbHost = '".str_replace("'", "\\'", $dbHostWithoutPort)."';\n");
					$file->write("\$dbPort = ".$dbPort.";\n");
					$file->write("\$dbUser = '".str_replace("'", "\\'", $dbUser)."';\n");
					$file->write("\$dbPassword = '".str_replace("'", "\\'", $dbPassword)."';\n");
					$file->write("\$dbName = '".str_replace("'", "\\'", $dbName)."';\n");
					$file->write("if (!defined('WCF_N')) define('WCF_N', $dbNumber);\n");
					$file->close();
					
					// go to next step
					$this->gotoNextStep('createDB');
					exit;
				}
				// show configure template again
				else {
					WCF::getTPL()->assign(['conflictedTables' => $conflictedTables]);
				}
			}
			catch (SystemException $e) {
				WCF::getTPL()->assign(['exception' => $e]);
			}
		}
		WCF::getTPL()->assign([
			'dbHost' => $dbHost,
			'dbUser' => $dbUser,
			'dbPassword' => $dbPassword,
			'dbName' => $dbName,
			'dbNumber' => $dbNumber,
			'nextStep' => 'configureDB'
		]);
		WCF::getTPL()->display('stepConfigureDB');
	}
	
	/**
	 * Checks if in the chosen database are tables in conflict with the wcf tables
	 * which will be created in the next step.
	 * 
	 * @param	\wcf\system\database\Database	$db
	 * @param	integer				$dbNumber
	 * @return	string[]	list of already existing tables
	 */
	protected function getConflictedTables($db, $dbNumber) {
		// get content of the sql structure file
		$sql = file_get_contents(TMP_DIR.'setup/db/install.sql');
		
		// installation number value 'n' (WCF_N) must be reflected in the executed sql queries
		$sql = str_replace('wcf1_', 'wcf'.$dbNumber.'_', $sql);
		
		// get all tablenames which should be created
		preg_match_all("%CREATE\s+TABLE\s+(\w+)%", $sql, $matches);
		
		// get all installed tables from chosen database
		$existingTables = $db->getEditor()->getTableNames();
		
		// check if existing tables are in conflict with wcf tables
		$conflictedTables = [];
		foreach ($existingTables as $existingTableName) {
			foreach ($matches[1] as $wcfTableName) {
				if ($existingTableName == $wcfTableName) {
					$conflictedTables[] = $wcfTableName;
				}
			}
		}
		return $conflictedTables;
	}
	
	/**
	 * Creates the database structure of the wcf.
	 */
	protected function createDB() {
		$this->initDB();
		
		// get content of the sql structure file
		$sql = file_get_contents(TMP_DIR.'setup/db/install.sql');
		
		// split by offsets
		$sqlData = explode('/* SQL_PARSER_OFFSET */', $sql);
		$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
		if (!isset($sqlData[$offset])) {
			throw new SystemException("Offset for SQL parser is out of bounds, ".$offset." was requested, but there are only ".count($sqlData)." sections");
		}
		$sql = $sqlData[$offset];
		
		// installation number value 'n' (WCF_N) must be reflected in the executed sql queries
		$sql = str_replace('wcf1_', 'wcf'.WCF_N.'_', $sql);
		
		// execute sql queries
		$parser = new SQLParser($sql);
		$parser->execute();
		
		// log sql queries
		preg_match_all("~CREATE\s+TABLE\s+(\w+)~i", $sql, $matches);
		
		if (!empty($matches[1])) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
						(sqlTable)
				VALUES		(?)";
			$statement = self::getDB()->prepareStatement($sql);
			foreach ($matches[1] as $tableName) {
				$statement->execute([$tableName]);
			}
		}
		
		if ($offset < (count($sqlData) - 1)) {
			WCF::getTPL()->assign([
				'__additionalParameters' => [
					'offset' => $offset + 1
				]
			]);
			
			$this->gotoNextStep('createDB');
		}
		else {
			/*
			 * Manually install PIPPackageInstallationPlugin since install.sql content is not escaped resulting
			* in different behaviour in MySQL and MSSQL. You SHOULD NOT move this into install.sql!
			*/
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_plugin
						(pluginName, priority, className)
				VALUES		(?, ?, ?)";
			$statement = self::getDB()->prepareStatement($sql);
			$statement->execute([
				'packageInstallationPlugin',
				1,
				'wcf\system\package\plugin\PIPPackageInstallationPlugin'
			]);
			
			$this->gotoNextStep('logFiles');
		}
	}
	
	/**
	 * Logs the unzipped files.
	 */
	protected function logFiles() {
		$this->initDB();
		
		$this->getInstalledFiles(WCF_DIR);
		$acpTemplateInserts = $fileInserts = [];
		foreach (self::$installedFiles as $file) {
			$match = [];
			if (preg_match('~^acp/templates/([^/]+)\.tpl$~', $file, $match)) {
				// acp template
				$acpTemplateInserts[] = $match[1];
			}
			else {
				// regular file
				$fileInserts[] = $file;
			}
		}
		
		// save acp template log
		if (!empty($acpTemplateInserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_acp_template
						(templateName, application)
				VALUES		(?, ?)";
			$statement = self::getDB()->prepareStatement($sql);
			
			self::getDB()->beginTransaction();
			foreach ($acpTemplateInserts as $acpTemplate) {
				$statement->execute([$acpTemplate, 'wcf']);
			}
			self::getDB()->commitTransaction();
		}
		
		// save file log
		if (!empty($fileInserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_file_log
						(filename, application)
				VALUES		(?, ?)";
			$statement = self::getDB()->prepareStatement($sql);
			
			self::getDB()->beginTransaction();
			foreach ($fileInserts as $file) {
				$statement->execute([$file, 'wcf']);
			}
			self::getDB()->commitTransaction();
		}
		
		$this->gotoNextStep('installLanguage');
	}
	
	/**
	 * Scans the given dir for installed files.
	 * 
	 * @param	string		$dir
	 * @throws      SystemException
	 */
	protected function getInstalledFiles($dir) {
		$logFile = $dir . 'files.log';
		if (!file_exists($logFile)) {
			throw new SystemException("Expected a valid file log at '".$logFile."'.");
		}
		
		self::$installedFiles = explode("\n", file_get_contents($logFile));
		
		@unlink($logFile);
	}
	
	/**
	 * Installs the selected languages.
	 */
	protected function installLanguage() {
		$this->initDB();
		
		foreach (self::$selectedLanguages as $language) {
			// get language.xml file name
			$filename = TMP_DIR.'install/lang/'.$language.'.xml';
			
			// check the file
			if (!file_exists($filename)) {
				throw new SystemException("unable to find language file '".$filename."'");
			}
			
			// open the file
			$xml = new XML();
			$xml->load($filename);
			
			// import xml
			LanguageEditor::importFromXML($xml, 0);
		}
		
		// set default language
		$language = LanguageFactory::getInstance()->getLanguageByCode(in_array(self::$selectedLanguageCode, self::$selectedLanguages) ? self::$selectedLanguageCode : self::$selectedLanguages[0]);
		LanguageFactory::getInstance()->makeDefault($language->languageID);
		
		// rebuild language cache
		LanguageCacheBuilder::getInstance()->reset();
		
		// go to next step
		$this->gotoNextStep('createUser');
	}
	
	/**
	 * Shows the page for creating the admin account.
	 */
	protected function createUser() {
		$errorType = $errorField = $username = $email = $confirmEmail = $password = $confirmPassword = '';
		
		$username = '';
		$email = $confirmEmail = '';
		$password = $confirmPassword = '';
		
		if (isset($_POST['send']) || self::$developerMode) {
			if (isset($_POST['send'])) {
				if (isset($_POST['username'])) $username = StringUtil::trim($_POST['username']);
				if (isset($_POST['email'])) $email = StringUtil::trim($_POST['email']);
				if (isset($_POST['confirmEmail'])) $confirmEmail = StringUtil::trim($_POST['confirmEmail']);
				if (isset($_POST['password'])) $password = $_POST['password'];
				if (isset($_POST['confirmPassword'])) $confirmPassword = $_POST['confirmPassword'];
			}
			else {
				$username = $password = $confirmPassword = 'root';
				$email = $confirmEmail = 'wsc-developer-mode@example.com';
			}
			
			// error handling
			try {
				// username
				if (empty($username)) {
					throw new UserInputException('username');
				}
				if (!UserUtil::isValidUsername($username)) {
					throw new UserInputException('username', 'invalid');
				}
				
				// e-mail address
				if (empty($email)) {
					throw new UserInputException('email');
				}
				if (!UserUtil::isValidEmail($email)) {
					throw new UserInputException('email', 'invalid');
				}
				
				// confirm e-mail address
				if ($email != $confirmEmail) {
					throw new UserInputException('confirmEmail', 'notEqual');
				}
				
				// password
				if (empty($password)) {
					throw new UserInputException('password');
				}
				
				// confirm e-mail address
				if ($password != $confirmPassword) {
					throw new UserInputException('confirmPassword', 'notEqual');
				}
				
				// no errors
				// init database connection
				$this->initDB();
				
				// get language id
				$languageID = 0;
				$sql = "SELECT	languageID
					FROM	wcf".WCF_N."_language
					WHERE	languageCode = ?";
				$statement = self::getDB()->prepareStatement($sql);
				$statement->execute([self::$selectedLanguageCode]);
				$row = $statement->fetchArray();
				if (isset($row['languageID'])) $languageID = $row['languageID'];
				
				if (!$languageID) {
					$languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
				}
				
				// create user
				$data = [
					'data' => [
						'email' => $email,
						'languageID' => $languageID,
						'password' => $password,
						'username' => $username,
						'signatureEnableHtml' => 1
					],
					'groups' => [
						1,
						3,
						4
					],
					'languages' => [
						$languageID
					]
				];
				
				$userAction = new UserAction([], 'create', $data);
				$userAction->executeAction();
				
				// go to next step
				$this->gotoNextStep('installPackages');
				exit;
			}
			catch (UserInputException $e) {
				$errorField = $e->getField();
				$errorType = $e->getType();
			}
		}
		
		WCF::getTPL()->assign([
			'errorField' => $errorField,
			'errorType' => $errorType,
			'username' => $username,
			'email' => $email,
			'confirmEmail' => $confirmEmail,
			'password' => $password,
			'confirmPassword' => $confirmPassword,
			'nextStep' => 'createUser'
		]);
		WCF::getTPL()->display('stepCreateUser');
	}
	
	/**
	 * Registers with wcf setup delivered packages in the package installation queue.
	 */
	protected function installPackages() {
		// init database connection
		$this->initDB();
		
		// get admin account
		$admin = new User(1);
		
		// get delivered packages
		$wcfPackageFile = '';
		$otherPackages = [];
		$tar = new Tar(SETUP_FILE);
		foreach ($tar->getContentList() as $file) {
			if ($file['type'] != 'folder' && mb_strpos($file['filename'], 'install/packages/') === 0) {
				$packageFile = basename($file['filename']);
				
				// ignore any files which aren't an archive
				if (preg_match('~\.(tar\.gz|tgz|tar)$~', $packageFile)) {
					$packageName = preg_replace('!\.(tar\.gz|tgz|tar)$!', '', $packageFile);
					
					if ($packageName == 'com.woltlab.wcf') {
						$wcfPackageFile = $packageFile;
					}
					else {
						$isStrato = (!empty($_SERVER['DOCUMENT_ROOT']) && (strpos($_SERVER['DOCUMENT_ROOT'], 'strato') !== false));
						if (!$isStrato && preg_match('!\.(tar\.gz|tgz)$!', $packageFile)) {
							// try to unzip zipped package files
							if (FileUtil::uncompressFile(TMP_DIR.'install/packages/'.$packageFile, TMP_DIR.'install/packages/'.$packageName.'.tar')) {
								@unlink(TMP_DIR.'install/packages/'.$packageFile);
								$packageFile = $packageName.'.tar';
							}
						}
						
						$otherPackages[$packageName] = $packageFile;
					}
				}
			}
		}
		$tar->close();
		
		// delete install files
		$installPhpDeleted = @unlink('./install.php');
		@unlink('./test.php');
		$wcfSetupTarDeleted = @unlink('./WCFSetup.tar.gz');
		
		// render page
		WCF::getTPL()->assign([
			'installPhpDeleted' => $installPhpDeleted,
			'wcfSetupTarDeleted' => $wcfSetupTarDeleted
		]);
		$output = WCF::getTPL()->fetch('stepInstallPackages');
		
		// register packages in queue
		// get new process id
		$sql = "SELECT	MAX(processNo) AS processNo
			FROM	wcf".WCF_N."_package_installation_queue";
		$statement = self::getDB()->prepareStatement($sql);
		$statement->execute();
		$result = $statement->fetchArray();
		$processNo = intval($result['processNo']) + 1;
		
		// search existing wcf package
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package
			WHERE	package = 'com.woltlab.wcf'";
		$statement = self::getDB()->prepareStatement($sql);
		$statement->execute();
		if (!$statement->fetchSingleColumn()) {
			if (empty($wcfPackageFile)) {
				throw new SystemException('the essential package com.woltlab.wcf is missing.');
			}
			
			// register essential wcf package
			$queue = PackageInstallationQueueEditor::create([
				'processNo' => $processNo,
				'userID' => $admin->userID,
				'package' => 'com.woltlab.wcf',
				'packageName' => 'WoltLab Suite Core',
				'archive' => TMP_DIR.'install/packages/'.$wcfPackageFile,
				'isApplication' => 1
			]);
		}
		
		// register all other delivered packages
		asort($otherPackages);
		foreach ($otherPackages as $packageName => $packageFile) {
			// extract packageName from archive's package.xml
			$archive = new PackageArchive(TMP_DIR.'install/packages/'.$packageFile);
			try {
				$archive->openArchive();
			}
			catch (\Exception $e) {
				// we've encountered a broken archive, revert everything and then fail
				$sql = "SELECT	queueID, parentQueueID
					FROM	wcf".WCF_N."_package_installation_queue";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute();
				$queues = $statement->fetchMap('queueID', 'parentQueueID');
				
				$queueIDs = [];
				/** @noinspection PhpUndefinedVariableInspection */
				$queueID = $queue->queueID;
				while ($queueID) {
					$queueIDs[] = $queueID;
					
					$queueID = isset($queues[$queueID]) ? $queues[$queueID] : 0;
				}
				
				// remove previously created queues
				if (!empty($queueIDs)) {
					$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
						WHERE		queueID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					WCF::getDB()->beginTransaction();
					foreach ($queueIDs as $queueID) {
						$statement->execute([$queueID]);
					}
					WCF::getDB()->commitTransaction();
				}
				
				// remove package files
				@unlink(TMP_DIR.'install/packages/'.$wcfPackageFile);
				foreach ($otherPackages as $otherPackageFile) {
					@unlink(TMP_DIR.'install/packages/'.$otherPackageFile);
				}
				
				// throw exception again
				throw new SystemException('', 0, '', $e);
			}
			
			/** @noinspection PhpUndefinedVariableInspection */
			$queue = PackageInstallationQueueEditor::create([
				'parentQueueID' => $queue->queueID,
				'processNo' => $processNo,
				'userID' => $admin->userID,
				'package' => $packageName,
				'packageName' => $archive->getLocalizedPackageInfo('packageName'),
				'archive' => TMP_DIR.'install/packages/'.$packageFile,
				'isApplication' => 1
			]);
		}
		
		// determine the (randomized) cookie prefix
		$useRandomCookiePrefix = true;
		if (self::$developerMode && DevtoolsSetup::getInstance()->forceStaticCookiePrefix()) {
			$useRandomCookiePrefix = false;
		}
		
		$prefix = 'wsc_';
		if ($useRandomCookiePrefix) {
			$cookieNames = array_keys($_COOKIE);
			while (true) {
				$prefix = 'wsc_' . substr(sha1((string) mt_rand()), 0, 6) . '_';
				$isValid = true;
				foreach ($cookieNames as $cookieName) {
					if (strpos($cookieName, $prefix) === 0) {
						$isValid = false;
						break;
					}
				}
				
				if ($isValid) {
					break;
				}
			}
			
			// the options have not been imported yet
			file_put_contents(WCF_DIR . 'cookiePrefix.txt', $prefix);
		}
		
		// login as admin
		define('COOKIE_PREFIX', $prefix);
		
		$factory = new ACPSessionFactory();
		$factory->load();
		
		SessionHandler::getInstance()->changeUser($admin);
		SessionHandler::getInstance()->register('masterPassword', 1);
		SessionHandler::getInstance()->register('__wcfSetup_developerMode', self::$developerMode);
		SessionHandler::getInstance()->register('__wcfSetup_directories', self::$directories);
		SessionHandler::getInstance()->register('__wcfSetup_imagick', ImagickImageAdapter::isSupported());
		SessionHandler::getInstance()->unregister('__changeSessionID');
		SessionHandler::getInstance()->update();
		
		// print page
		HeaderUtil::sendHeaders();
		echo $output;
		
		// delete tmp files
		$directory = TMP_DIR.'/';
		DirectoryUtil::getInstance($directory)->removePattern(new Regex('\.tar(\.gz)?$'), true);
	}
	
	/**
	 * Goes to the next step.
	 * 
	 * @param	string		$nextStep
	 */
	protected function gotoNextStep($nextStep) {
		WCF::getTPL()->assign(['nextStep' => $nextStep]);
		WCF::getTPL()->display('stepNext');
	}
	
	/**
	 * Installs the files of the tar archive.
	 */
	protected static function installFiles() {
		$fileHandler = new SetupFileHandler();
		new Installer(self::$directories['wcf'], SETUP_FILE, $fileHandler, 'install/files/');
		$fileHandler->dumpToFile(self::$directories['wcf'] . 'files.log');
	}
	
	/**
	 * Reads the package names of the bundled applications in WCFSetup.tar.gz.
	 */
	protected static function getPackageNames() {
		// get package name
		$packageNames = [];
		$tar = new Tar(SETUP_FILE);
		foreach ($tar->getContentList() as $file) {
			if ($file['type'] != 'folder' && mb_strpos($file['filename'], 'install/packages/') === 0) {
				$packageFile = basename($file['filename']);
				
				try {
					$archive = new PackageArchive(TMP_DIR.'install/packages/'.$packageFile);
					$archive->openArchive();
					$packageNames[] = $archive->getLocalizedPackageInfo('packageName');
					$archive->getTar()->close();
				}
				catch (SystemException $e) {}
			}
		}
		$tar->close();
		
		sort($packageNames);
		
		// assign package name
		WCF::getTPL()->assign(['setupPackageNames' => $packageNames]);
	}
}
