<?php
namespace wcf\system;
use wcf\data\language\LanguageEditor;
use wcf\data\language\SetupLanguage;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\cache\CacheHandler;
use wcf\system\database\util\SQLParser;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\io\File;
use wcf\system\io\Tar;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageArchive;
use wcf\system\session\ACPSessionFactory;
use wcf\system\session\SessionHandler;
use wcf\system\setup\Installer;
use wcf\system\template\SetupTemplateEngine;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;
use wcf\util\XML;

// define
define('PACKAGE_ID', '0');
define('HTTP_ENABLE_NO_CACHE_HEADERS', 0);
define('HTTP_ENABLE_GZIP', 0);
define('HTTP_GZIP_LEVEL', 0);
define('CACHE_SOURCE_TYPE', 'disk');
define('MODULE_MASTER_PASSWORD', 1);
define('ENABLE_DEBUG_MODE', 1);
define('ENABLE_BENCHMARK', 1);

/**
 * WCFSetup executes the installation of the basic wcf systems.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category 	Community Framework
 */
class WCFSetup extends WCF {
	protected static $availableLanguages = array();
	protected static $selectedLanguageCode = 'en';
	protected static $selectedLanguages = array();
	protected static $wcfDir = '';
	protected static $installedFiles = array();
	protected static $setupPackageName = 'WoltLab Community Framework';
	protected static $developerMode = 0;
	protected static $dbClasses = array(
		'MySQLDatabase' => array('class' => 'wcf\system\database\MySQLDatabase', 'minversion' => '5.1.17'),	// MySQL 5.0.0+
		'PostgreSQLDatabase' => array('class' => 'wcf\system\database\PostgreSQLDatabase', 'minversion' => '8.2.0')	// PostgreSQL 8.2.0+
	);
	
	/**
	 * Calls all init functions of the WCFSetup class and starts the setup process.
	 */
	public function __construct() {
		@set_time_limit(0);
		$this->initMagicQuotes();
		$this->getDeveloperMode();
		$this->getLanguageSelection();
		$this->getWCFDir();
		$this->initLanguage();
		$this->initTPL();
		self::getLanguage()->loadLanguage();
		$this->getPackageName();
		
		// start setup
		$this->setup();
	}
	
	/**
	 * Gets the status of the developer mode.
	 */
	protected static function getDeveloperMode() {
		if (isset($_GET['dev'])) self::$developerMode = intval($_GET['dev']);
		else if (isset($_POST['dev'])) self::$developerMode = intval($_POST['dev']);
	}
	
	/**
	 * Gets the selected language.
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
	 * Gets the available database classes.
	 *
	 * @return	array
	 */
	protected static function getAvailableDBClasses() {
		$availableDBClasses = array();
		foreach (self::$dbClasses as $class => $data) {
			if (call_user_func(array($data['class'], 'isSupported'))) {
				$availableDBClasses[$class] = $data;
			}
		}
		
		return $availableDBClasses;
	}

	/**
	 * Gets the selected wcf dir from request.
	 */
	protected static function getWCFDir() {
		if (isset($_REQUEST['wcfDir']) && $_REQUEST['wcfDir'] != '') {
			self::$wcfDir = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator($_REQUEST['wcfDir']));
			if (@file_exists(self::$wcfDir)) {
				define('RELATIVE_WCF_DIR', FileUtil::getRelativePath(INSTALL_SCRIPT_DIR, self::$wcfDir));
			}
		}
		
		define('WCF_DIR', self::$wcfDir);
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
		self::$languageObj = new SetupLanguage(null, array(
			'languageCode' => self::$selectedLanguageCode
		));
	}
	
	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		self::$tplObj = SetupTemplateEngine::getInstance();
		self::getTPL()->setLanguageID((self::$selectedLanguageCode == 'en' ? 0 : 1));
		self::getTPL()->setCompileDir(TMP_DIR);
		self::getTPL()->addTemplatePath(PACKAGE_ID, TMP_DIR);
		self::getTPL()->registerPrefilter(array('lang'));
		self::getTPL()->assign(array(
			'__wcf' => $this,
			'tmpFilePrefix' => TMP_FILE_PREFIX,
			'languageCode' => self::$selectedLanguageCode,
			'selectedLanguages' => self::$selectedLanguages,
			'wcfDir' => self::$wcfDir,
			'developerMode' => self::$developerMode
		));
	}
	
	/**
	 * Returns all languages from WCFSetup.tar.gz.
	 *
	 * @return	array
	 */
	protected static function getAvailableLanguages() {
		$languages = $match = array();
		$tar = new Tar(SETUP_FILE);
		foreach ($tar->getContentList() as $file) {
			if (strpos($file['filename'], 'setup/lang/') === 0 && substr($file['filename'], -4) == '.xml') {
				$xml = new XML();
				$xml->load(TMP_DIR.$file['filename']);
				$languageCode = LanguageEditor::readLanguageCodeFromXML($xml);
				$languageName = LanguageEditor::readLanguageNameFromXML($xml);
				
				$languages[$languageCode] = $languageName;
			}
		}
		$tar->close();

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
		$progress = round((100 / 12) * ++$currentStep, 0);
		self::getTPL()->assign(array('progress' => $progress));
	}
	
	/**
	 * Executes the setup steps.
	 */
	protected function setup() {
		// get current step
		if (isset($_REQUEST['step'])) $step = $_REQUEST['step'];
		else $step = 'selectSetupLanguage';

		// execute current step
		switch ($step) {
			case 'selectSetupLanguage':
				if (!self::$developerMode) {
					$this->calcProgress(0);
					$this->selectSetupLanguage();
					break;
				}
				
			case 'showLicense':
				if (!self::$developerMode) {
					$this->calcProgress(1);
					$this->showLicense();
					break;
				}
				
			case 'showSystemRequirements':
				if (!self::$developerMode) {
					$this->calcProgress(2);
					$this->showSystemRequirements();
					break;
				}
				
			case 'searchWcfDir':
				$this->calcProgress(3);
				$this->searchWcfDir();
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
				$this->calcProgress(7);
				$this->createDB();
				break;
				
			case 'logFiles':
				$this->calcProgress(8);
				$this->logFiles();
				break;
				
			case 'installLanguage':
				$this->calcProgress(9);
				$this->installLanguage();
				break;
				
			case 'createUser':
				$this->calcProgress(10);
				$this->createUser();
				break;
				
			case 'installPackages':
				$this->calcProgress(11);
				$this->installPackages();
				break;
		}
	}
	
	/**
	 * Shows the first setup page.
	 */
	protected function selectSetupLanguage() {
		WCF::getTPL()->assign(array(
			'availableLanguages' => self::$availableLanguages,
			'nextStep' => 'showLicense'
		));
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
				WCF::getTPL()->assign(array('missingAcception' => true));
			}
		
		}
		
		if (file_exists(TMP_DIR.'setup/license/license_'.self::$selectedLanguageCode.'.txt')) {
			$license = file_get_contents(TMP_DIR.'setup/license/license_'.self::$selectedLanguageCode.'.txt');
		}
		else {
			$license = file_get_contents(TMP_DIR.'setup/license/license_en.txt');
		}
		
		WCF::getTPL()->assign(array(
			'license' => $license,
			'nextStep' => 'showLicense'
		));
		WCF::getTPL()->display('stepShowLicense');
	}
	
	/**
	 * Shows the system requirements.
	 */
	protected function showSystemRequirements() {
		$system = array();
		
		// php version
		$system['phpVersion']['value'] = phpversion();
		$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $system['phpVersion']['value']);
		$system['phpVersion']['result'] = (version_compare($comparePhpVersion, '5.3.0') >= 0);
		
		// sql
		$system['sql']['value'] = array_keys(self::getAvailableDBClasses());
		$system['sql']['result'] = count($system['sql']['value']) > 0;
		
		// upload_max_filesize
		$system['uploadMaxFilesize']['value'] = ini_get('upload_max_filesize');
		$system['uploadMaxFilesize']['result'] = (intval($system['uploadMaxFilesize']['value']) > 0);
		
		// gdlib version
		$system['gdLib']['value'] = '0.0.0';
		if (function_exists('gd_info')) {
			$temp = gd_info();
			$match = array();
			if (preg_match('!([0-9]+\.[0-9]+(?:\.[0-9]+)?)!', $temp['GD Version'], $match)) {
				if (preg_match('/^[0-9]+\.[0-9]+$/', $match[1])) $match[1] .= '.0';
				$system['gdLib']['value'] = $match[1];
			}
		}
		$system['gdLib']['result'] = (version_compare($system['gdLib']['value'], '2.0.0') >= 0);
		
		// mb string
		$system['mbString']['result'] = extension_loaded('mbstring');
		
		WCF::getTPL()->assign(array(
			'system' => $system,
			'nextStep' => 'searchWcfDir'
		));
		WCF::getTPL()->display('stepShowSystemRequirements');
	}
	
	/**
	 * Searches the wcf dir.
	 */
	protected function searchWcfDir() {
		$foundDirectory = '';
		if (self::$wcfDir) {
			$wcfDir = self::$wcfDir;
		}
		else {
			if ($foundDirectory = FileUtil::scanFolder(INSTALL_SCRIPT_DIR, "WCF.class.php", true)) {
				$foundDirectory = $wcfDir = FileUtil::unifyDirSeperator(dirname(dirname(dirname($foundDirectory))).'/');
				
				if (dirname(dirname($wcfDir)).'/' == TMP_DIR) {
					$foundDirectory = false;
					$wcfDir = FileUtil::unifyDirSeperator(INSTALL_SCRIPT_DIR).'wcf/';
				}
			}
			else {
				$wcfDir = FileUtil::unifyDirSeperator(INSTALL_SCRIPT_DIR).'wcf/';
			}
		}
		
		// domain
		$domainName = '';
		if (!empty($_SERVER['SERVER_NAME'])) $domainName = 'http://' . $_SERVER['SERVER_NAME'];
		// port
		if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) $domainName .= ':' . $_SERVER['SERVER_PORT'];
		// script url
		$installScriptUrl = '';
		if (!empty($_SERVER['REQUEST_URI'])) $installScriptUrl = FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash(FileUtil::unifyDirSeperator(dirname($_SERVER['REQUEST_URI']))));
		
		WCF::getTPL()->assign(array(
			'nextStep' => 'unzipFiles',
			'foundDirectory' => $foundDirectory,
			'wcfDir' => $wcfDir,
			'domainName' => $domainName,
			'installScriptUrl' => $installScriptUrl,
			'installScriptDir' => FileUtil::unifyDirSeperator(INSTALL_SCRIPT_DIR)
		));
		
		WCF::getTPL()->display('stepSearchWcfDir');
	}
	
	/**
	 * Unzips the files of the wcfsetup tar archive.
	 */
	protected function unzipFiles() {
		// WCF seems to be installed, skip installation of
		// files, database and admin account
		// and go directly to the installation of packages
		if (@is_file(self::$wcfDir.'lib/system/WCF.class.php')) {
			$this->gotoNextStep('installPackages');
			exit;
		}
		// WCF not yet installed, install files first
		else {
			try {
				$this->installFiles();
			}
			catch (\Exception $e) {
				WCF::getTPL()->assign(array('exception' => $e));
				$this->searchWcfDir();
				return;
			}
			
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
			self::$selectedLanguages = array();
			foreach (self::$availableLanguages as $languageCode => $language) {
				self::$selectedLanguages[] = $languageCode;
			}
			
			self::getTPL()->assign(array('selectedLanguages' => self::$selectedLanguages));
			$this->gotoNextStep('configureDB');
			exit;
		}
		
		// start error handling
		if (isset($_POST['send'])) {
			try {
				// no languages selected
				if (count(self::$selectedLanguages) == 0) {
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
			WCF::getTPL()->assign(array('selectedLanguages' => self::$selectedLanguages));
		}
		
		WCF::getTPL()->assign(array(
			'errorField' => $errorField,
			'errorType' => $errorType,
			'availableLanguages' => self::$availableLanguages,
			'nextStep' => 'selectLanguages'
		));
		WCF::getTPL()->display('stepSelectLanguages');
	}
	
	/**
	 * Shows the page for configurating the database connection.
	 */
	protected function configureDB() {
		$availableDBClasses = self::getAvailableDBClasses();
		$dbHost = 'localhost';
		$dbUser = 'root';
		$dbPassword = '';
		$dbName = 'wcf';
		$dbNumber = '1';
		$dbClass = 'MySQLDatabase';
		if (!function_exists('mysql_connect')) $dbClass = 'MySQLiDatabase';
		$overwriteTables = false;
		
		if (isset($_POST['send'])) {
			if (isset($_POST['dbHost'])) $dbHost = $_POST['dbHost'];
			if (isset($_POST['dbUser'])) $dbUser = $_POST['dbUser'];
			if (isset($_POST['dbPassword'])) $dbPassword = $_POST['dbPassword'];
			if (isset($_POST['dbName'])) $dbName = $_POST['dbName'];
			if (isset($_POST['overwriteTables'])) $overwriteTables = intval($_POST['overwriteTables']);
			// Should the user not be prompted if converted or default n match an
			// existing installation number? By now the existing installation
			// will be overwritten just so!
			if (isset($_POST['dbNumber'])) $dbNumber = intval($_POST['dbNumber']);
			if (isset($_POST['dbClass'])) $dbClass = $_POST['dbClass'];
			
			// get port
			$dbPort = 0;
			if (preg_match('/^(.+?):(\d+)$/', $dbHost, $match)) {
				$dbHost = $match[1];
				$dbPort = intval($match[2]);
			}
			
			// test connection
			try {
				// check db class
				$validDB = false;
				foreach ($availableDBClasses as $dbData) {
					if ($dbData['class'] == $dbClass) {
						$validDB = true;
						break;
					}
				}
				
				if (!$validDB) {
					throw new SystemException("Database type '".$dbClass."'. is not available on this system.");
				}
				
				// check connection data
				$db = new $dbClass($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);
				$db->connect();
				
				// check sql version
				if (!empty($availableDBClasses[$dbClass]['minversion'])) {
					$sqlVersion = $db->getVersion();
					if ($sqlVersion != 'unknown') {
						$compareSQLVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $sqlVersion);
						if (!(version_compare($compareSQLVersion, $availableDBClasses[$dbClass]['minversion']) >= 0)) {
							throw new SystemException("Insufficient SQL version '".$compareSQLVersion."'. Version '".$availableDBClasses[$dbClass]['minversion']."' or greater is needed.");
						}
					}
				}
				
				// check for table conflicts
				$conflictedTables = $this->getConflictedTables($db, $dbNumber);
				if (!empty($conflictedTables) && ($overwriteTables || self::$developerMode)) {
					// remove tables
					$db->getEditor()->dropConflictedTables($conflictedTables);
				}
				
				// write config.inc
				if (empty($conflictedTables) || $overwriteTables || self::$developerMode) {
					// connection successfully established
					// write configuration to config.inc.php
					$file = new File(WCF_DIR.'config.inc.php');
					$file->write("<?php\n");
					$file->write("\$dbHost = '".StringUtil::replace("'", "\\'", $dbHost)."';\n");
					$file->write("\$dbPort = ".$dbPort.";\n");
					$file->write("\$dbUser = '".StringUtil::replace("'", "\\'", $dbUser)."';\n");
					$file->write("\$dbPassword = '".StringUtil::replace("'", "\\'", $dbPassword)."';\n");
					$file->write("\$dbName = '".StringUtil::replace("'", "\\'", $dbName)."';\n");
					$file->write("\$dbClass = '".StringUtil::replace("'", "\\'", $dbClass)."';\n");
					$file->write("if (!defined('WCF_N')) define('WCF_N', $dbNumber);\n?>");
					$file->close();
				
					// go to next step
					$this->gotoNextStep('createDB');
					exit;
				}
				// show configure temnplate again
				else {
					WCF::getTPL()->assign(array('conflictedTables' => $conflictedTables));
				}
			}
			catch (SystemException $e) {
				WCF::getTPL()->assign(array('exception' => $e));
			}
		}
		WCF::getTPL()->assign(array(
			'dbHost' => $dbHost,
			'dbUser' => $dbUser,
			'dbPassword' => $dbPassword,
			'dbName' => $dbName,
			'dbNumber' => $dbNumber,
			'dbClass' => $dbClass,
			'availableDBClasses' => $availableDBClasses,
			'nextStep' => 'configureDB'
		));
		WCF::getTPL()->display('stepConfigureDB');
	}
	
	
	/**
	 * Checks if in the chosen database are tables in conflict with the wcf tables
	 * which will be created in the next step.
	 *
	 * @param	Database	$db
	 * @param 	integer		$dbNumber
	 */
	protected function getConflictedTables($db, $dbNumber) {
		// get content of the sql structure file
		$sql = file_get_contents(TMP_DIR.'setup/db/install.sql');
		
		// installation number value 'n' (WCF_N) must be reflected in the executed sql queries
		$sql = StringUtil::replace('wcf1_', 'wcf'.$dbNumber.'_', $sql);
		
		// get all tablenames which should be created
		preg_match_all("%CREATE\s+TABLE\s+(\w+)%", $sql, $matches);
		
		// get all installed tables from chosen database
		$existingTables = $db->getEditor()->getTableNames();
		
		// check if existing tables are in conflict with wcf tables
		$conflictedTables = array();
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
		
		// installation number value 'n' (WCF_N) must be reflected in the executed sql queries
		$sql = StringUtil::replace('wcf1_', 'wcf'.WCF_N.'_', $sql);
		
		// execute sql queries
		$parser = new SQLParser($sql);
		$parser->execute();
		
		// log sql queries
		preg_match_all("~CREATE\s+TABLE\s+(\w+)~i", $sql, $matches);
		
		if (count($matches[1])) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
						(sqlTable)
				VALUES		(?)";
			$statement = self::getDB()->prepareStatement($sql);
			foreach ($matches[1] as $tableName) {
				$statement->execute(array($tableName));
			}
		}
		
		/*
		 * Manually install PIPPackageInstallationPlugin since install.sql content is not escaped resulting
		 * in different behaviour in MySQL and MSSQL. You SHOULD NOT move this into install.sql!
		 */
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_plugin
					(pluginName, priority, className)
			VALUES		(?, ?, ?)";
		$statement = self::getDB()->prepareStatement($sql);
		$statement->execute(array(
			'packageInstallationPlugin',
			1,
			'wcf\system\package\plugin\PIPPackageInstallationPlugin'
		));
		
		$this->gotoNextStep('logFiles');
	}
	
	/**
	 * Logs the unzipped files.
	 */
	protected function logFiles() {
		$this->initDB();
		
		$this->getInstalledFiles(WCF_DIR);
		$acpTemplateInserts = $fileInserts = array();
		foreach (self::$installedFiles as $file) {
			$match = array();
			if (preg_match('!/acp/templates/([^/]+)\.tpl$!', $file, $match)) {
				// acp template
				$acpTemplateInserts[] = $match[1];
			}
			else {
				// regular file
				$fileInserts[] = StringUtil::replace(WCF_DIR, '', $file);
			}
		}
		
		// save acp template log
		if (!empty($acpTemplateInserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_acp_template
						(templateName)
				VALUES		(?)";
			$statement = self::getDB()->prepareStatement($sql);
			
			self::getDB()->beginTransaction();
			foreach ($acpTemplateInserts as $acpTemplate) {
				$statement->executeUnbuffered(array($acpTemplate));
			}
			self::getDB()->commitTransaction();
		}
		
		// save file log
		if (!empty($fileInserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_file_log
						(filename)
				VALUES		(?)";
			$statement = self::getDB()->prepareStatement($sql);
			
			self::getDB()->beginTransaction();
			foreach ($fileInserts as $file) {
				$statement->executeUnbuffered(array($file));
			}
			self::getDB()->commitTransaction();
		}
		
		$this->gotoNextStep('installLanguage');
	}
	
	/**
	 * Scans the given dir for installed files.
	 *
	 * @param 	string		$dir
	 */
	protected function getInstalledFiles($dir) {
		if ($files = glob($dir.'*')) {
			foreach ($files as $file) {
				if (is_dir($file)) {
					$this->getInstalledFiles(FileUtil::addTrailingSlash($file));
				}
				else {
					self::$installedFiles[] = FileUtil::unifyDirSeperator($file);
				}
			}
		}
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
		
		// assign all languages to package id 0
		$sql = "SELECT	languageID
			FROM	wcf".WCF_N."_language";
		$statement = self::getDB()->prepareStatement($sql);
		$statement->execute();
		$languages = array();
		while ($row = $statement->fetchArray()) {
			$languages[] = $row['languageID'];
		}
		
		if (count($languages) > 0) {
			$sql = "INSERT INTO	wcf".WCF_N."_language_to_package
						(languageID)
				VALUES		(?)";
			$statement = self::getDB()->prepareStatement($sql);
			
			foreach ($languages as $languageID) {
				$statement->execute(array(
					$languageID
				));
			}
		}
		
		// rebuild language cache
		CacheHandler::getInstance()->clearResource('languages');
		
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
				if (isset($_POST['username'])) 		$username = StringUtil::trim($_POST['username']);
				if (isset($_POST['email'])) 		$email = StringUtil::trim($_POST['email']);
				if (isset($_POST['confirmEmail'])) 	$confirmEmail = StringUtil::trim($_POST['confirmEmail']);
				if (isset($_POST['password'])) 		$password = $_POST['password'];
				if (isset($_POST['confirmPassword'])) 	$confirmPassword = $_POST['confirmPassword'];
			}
			else {
				$username = $password = $confirmPassword = 'root';
				$email = $confirmEmail = 'woltlab@woltlab.com';
			}
			
			// error handling
			try {
				// username
				if (empty($username)) {
					throw new UserInputException('username');
				}
				if (!UserUtil::isValidUsername($username)) {
					throw new UserInputException('username', 'notValid');
				}
				
				// e-mail address
				if (empty($email)) {
					throw new UserInputException('email');
				}
				if (!UserUtil::isValidEmail($email)) {
					throw new UserInputException('email', 'notValid');
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
				$statement->execute(array(self::$selectedLanguageCode));
				$row = $statement->fetchArray();
				if (isset($row['languageID'])) $languageID = $row['languageID'];
				
				// create user
				$data = array(
					'data' => array(
						'email' => $email,
						'languageID' => $languageID,
						'password' => $password,
						'username' => $username
					),
					'groups' => array(
						1,
						3,
						4
					),
					'languages' => array(
						$languageID
					)
				);
				
				$userAction = new UserAction(array(), 'create', $data);
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
		
		WCF::getTPL()->assign(array(
			'errorField' => $errorField,
			'errorType' => $errorType,
			'username' => $username,
			'email' => $email,
			'confirmEmail' => $confirmEmail,
			'password' => $password,
			'confirmPassword' => $confirmPassword,
			'nextStep' => 'createUser'
		));
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
		$otherPackages = array();
		$tar = new Tar(SETUP_FILE);
		foreach ($tar->getContentList() as $file) {
			if ($file['type'] != 'folder' && StringUtil::indexOf($file['filename'], 'install/packages/') === 0) {
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
		$row = $statement->fetchArray();
		if (!$row['count']) {
			if (empty($wcfPackageFile)) {
				throw new SystemException('the essential package com.woltlab.wcf is missing.');
			}
			
			// register essential wcf package
			$queue = PackageInstallationQueueEditor::create(array(
				'processNo' => $processNo,
				'userID' => $admin->userID,
				'package' => 'com.woltlab.wcf',
				'packageName' => 'WoltLab Community Framework',
				'archive' => TMP_DIR.'install/packages/'.$wcfPackageFile
			));
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
				// TODO: Maybe break the installation if archive is broken?
				// this is a broken archive, skip it
				continue;
			}
			
			$queue = PackageInstallationQueueEditor::create(array(
				'parentQueueID' => $queue->queueID,
				'processNo' => $processNo,
				'userID' => $admin->userID,
				'package' => $packageName,
				'packageName' => $archive->getPackageInfo('packageName'),
				'archive' => TMP_DIR.'install/packages/'.$packageFile
			));
		}
		
		// login as admin
		$factory = new ACPSessionFactory();
		$factory->load();
		
		SessionHandler::getInstance()->changeUser($admin);
		SessionHandler::getInstance()->register('masterPassword', 1);
		SessionHandler::getInstance()->update();
		
		// TODO: print message if delete fails
		$installPhpDeleted = @unlink('./install.php');
		$testPhpDeleted = @unlink('./test.php');
		$wcfSetupTarDeleted = @unlink('./WCFSetup.tar.gz');
		
		// print page
		WCF::getTPL()->assign(array(
			'installPhpDeleted' => $installPhpDeleted,
			'wcfSetupTarDeleted' => $wcfSetupTarDeleted
		));
		WCF::getTPL()->display('stepInstallPackages');
		
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
		WCF::getTPL()->assign(array('nextStep' => $nextStep));
		WCF::getTPL()->display('stepNext');
	}
	
	/**
	 * Installs the files of the tar archive.
	 */
	protected static function installFiles() {
		new Installer(self::$wcfDir, SETUP_FILE, null, 'install/files/');
	}
	
	/**
	 * Gets the package name of the first application in WCFSetup.tar.gz.
	 */
	protected static function getPackageName() {
		// get package name
		$tar = new Tar(SETUP_FILE);
		foreach ($tar->getContentList() as $file) {
			if ($file['type'] != 'folder' && StringUtil::indexOf($file['filename'], 'install/packages/') === 0) {
				$packageFile = basename($file['filename']);
				$packageName = preg_replace('!\.(tar\.gz|tgz|tar)$!', '', $packageFile);
				
				if ($packageName != 'com.woltlab.wcf') {
					try {
						$archive = new PackageArchive(TMP_DIR.'install/packages/'.$packageFile);
						$archive->openArchive();
						self::$setupPackageName = $archive->getPackageInfo('packageName');
						$archive->getTar()->close();
						break;
					}
					catch (SystemException $e) {}
				}
			}
		}
		$tar->close();
		
		// assign package name
		WCF::getTPL()->assign(array('setupPackageName' => self::$setupPackageName));
	}
}
