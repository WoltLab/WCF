<?php
namespace wcf\system;
use phpline\console\ConsoleReader;
use phpline\internal\AnsiUtil;
use phpline\internal\Log;
use phpline\TerminalFactory;
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
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;
use wcf\util\XML;
use Zend\Console\Adapter\Posix;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Getopt as ArgvParser;
use Zend\Loader\StandardAutoloader as ZendLoader;

/**
 * Executes the installation of the basic WCF systems via CLI.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category	Community Framework
 */
class CLIWCFSetup extends WCFSetup {
	/**
	 * instance of ConsoleReader
	 * @var phpline\console\ConsoleReader
	 */
	protected static $consoleReader = null;
	
	/**
	 * instance of ArgvParser
	 * @var Zend\Console\Getopt
	 */
	protected static $argvParser = null;
	
	/**
	 * Calls all init functions of the WCFSetup class and starts the setup process.
	 */
	public function __construct() {
		// register additional autoloaders
		require_once(__DIR__.'/api/phpline/phpline.phar');
		require_once(__DIR__.'/api/zend/Loader/StandardAutoloader.php');
		$zendLoader = new ZendLoader(array(ZendLoader::AUTOREGISTER_ZF => true));
		$zendLoader->register();
		
		$this->initArgv();
		$this->initPHPLine();
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
	 * Initializes parsing of command line options.
	 */
	protected function initArgv() {
		// initialise ArgvParser
		self::$argvParser = new ArgvParser(array(
			'language=s' => 'Selects the given language',
			'h|help' => 'Shows this help.',
			'dev|developer' => 'Executes WCFSetup in developer mode.'
		));
		self::getArgvParser()->setOptions(array(
			ArgvParser::CONFIG_CUMULATIVE_FLAGS => true,
			ArgvParser::CONFIG_DASHDASH => false
		));
		
		try {
			self::getArgvParser()->parse();
		}
		catch (ArgvException $e) {
			// show error message and usage
			echo $e->getMessage().PHP_EOL;
			echo self::getArgvParser()->getUsageMessage();
			exit;
		}
		
		// handle arguments
		if (self::getArgvParser()->help) {
			// show usage
			echo self::getArgvParser()->getUsageMessage();
			exit;
		}
		
		if (in_array('moo', self::getArgvParser()->getRemainingArgs())) {
			echo '...."Have you mooed today?"...'.PHP_EOL;
		}
	}
	
	/**
	 * Returns the argv parser.
	 *
	 * @return Zend\Console\Getopt
	 */
	public static function getArgvParser() {
		return self::$argvParser;
	}
	
	/**
	 * Initializes PHPLine.
	 */
	protected function initPHPLine() {
		$terminal = TerminalFactory::get();
		self::$consoleReader = new ConsoleReader("WoltLab Community Framework", null, null, $terminal);
	
		self::getReader()->setExpandEvents(false);
		self::getReader()->setHistoryEnabled(false);
	}
	
	/**
	 * Returns ConsoleReader.
	 *
	 * @return phpline\console\ConsoleReader
	 */
	public static function getReader() {
		return self::$consoleReader;
	}
	
	/**
	 * Returns the terminal that is attached to ConsoleReader
	 *
	 * @return phpline\Terminal
	 */
	public static function getTerminal() {
		return self::getReader()->getTerminal();
	}
	
	/**
	 * Gets the status of the developer mode.
	 */
	protected static function getDeveloperMode() {
		self::$developerMode = (boolean) self::getArgvParser()->developer;
	}
	
	/**
	 * Gets the selected language.
	 */
	protected static function getLanguageSelection() {
		self::$availableLanguages = self::getAvailableLanguages();
		
		if (self::getArgvParser()->language && isset(self::$availableLanguages[self::getArgvParser()->language])) {
			self::$selectedLanguageCode = self::getArgvParser()->language;
		}
		
		if (isset($_POST['selectedLanguages']) && is_array($_POST['selectedLanguages'])) {
			self::$selectedLanguages = $_POST['selectedLanguages'];
		}
	}
	
	/**
	 * Gets the selected wcf dir from request.
	 */
	protected static function getWCFDir() {
		define('WCF_DIR', self::$wcfDir);
	}
	
	/**
	 * Executes the setup steps.
	 */
	protected function setup($step = 'selectSetupLanguage') {
		// execute current step
		switch ($step) {
			case 'selectSetupLanguage':
				if (!self::$developerMode && !self::getArgvParser()->language) {
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
		self::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.global.welcome'));
		self::getReader()->println(StringUtil::stripHTML(WCF::getLanguage()->getDynamicVariable('wcf.global.welcome.description')));
		$languageChooser = "\n";
		foreach (self::getAvailableLanguages() as $languageCode => $languageName) {
			$languageChooser .= '   '.$languageName.' ('.$languageCode.')';
			if ($languageCode == self::$selectedLanguageCode) {
				$languageChooser .= '*';
			}
			$languageChooser .= PHP_EOL;
		}
		WCF::getTPL()->assign(array(
			'languageChooser' => $languageChooser
		));
		self::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.global.welcome.language.description'));
		
		$code = self::getReader()->readLine(WCF::getLanguage()->getDynamicVariable('wcf.global.welcome.language.change').'> ');
		if ($code === null) exit;
		$code = StringUtil::trim($code);
		if (isset(self::$availableLanguages[$code])) {
			self::$selectedLanguageCode = $code;
		}
		
		$this->initLanguage();
		$this->initTPL();
		self::getLanguage()->loadLanguage();
		$this->gotoNextStep('showLicense');
	}
	
	/**
	 * Shows the license agreement.
	 */
	protected function showLicense() {
		if (file_exists(TMP_DIR.'setup/license/license_'.self::$selectedLanguageCode.'.txt')) {
			$license = file_get_contents(TMP_DIR.'setup/license/license_'.self::$selectedLanguageCode.'.txt');
		}
		else {
			$license = file_get_contents(TMP_DIR.'setup/license/license_en.txt');
		}
		
		$license = explode("\n", $license);
		
		$width = self::getTerminal()->getWidth();
		$height = self::getTerminal()->getHeight();
		
		$showLines = $height - 1; // page limit
		
		foreach ($license as $line) {
			self::getReader()->println($line);
			
			if (--$showLines == 0) {
				// Overflow
				self::getReader()->flush();
				$c = self::getReader()->readCharacter();
				if ($c === "\r" || $c === "\n") {
					// one step forward
					$showLines = 1;
				}
				else if ($c !== 'q') {
					// page forward
					$showLines = $height - 1;
				}
				
				if ($c === 'q') {
					// cancel
					break;
				}
			}
		}
		
		$line = self::getReader()->readLine(PHP_EOL.WCF::getLanguage()->getDynamicVariable('wcf.global.license.accept.description').' [YN]> ');
		if ($line === null) exit;
		$line = StringUtil::trim($line);
		if (strtolower($line) == 'y') {
			$this->gotoNextStep('showSystemRequirements');
		}
		else {
			exit;
		}
	}
	
	/**
	 * Shows the system requirements.
	 */
	protected function showSystemRequirements() {
		$system = array();
		
		// php version
		$system['phpVersion']['value'] = phpversion();
		$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $system['phpVersion']['value']);
		$system['phpVersion']['result'] = (version_compare($comparePhpVersion, '5.3.2') >= 0);
		
		// sql
		$system['sql']['value'] = array_keys(self::getAvailableDBClasses());
		$system['sql']['result'] = !empty($system['sql']['value']);
		
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
		
		
		self::getReader()->println('Requirements: bla'.PHP_EOL); // TODO: show requirements
		if ($system['phpVersion']['result'] && $system['sql']['result'] && $system['mbString']['result']) {
			$this->gotoNextStep('searchWcfDir');
		}
		else {
			exit;
		}
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
		
		WCF::getTPL()->assign(array(
			'foundDirectory' => $foundDirectory,
			'wcfDir' => $wcfDir,
		));
		
		if (WCF::getTPL()->get('exception')) {
			self::getReader()->println(StringUtil::stripHTML(WCF::getLanguage()->getDynamicVariable('wcf.global.wcfDir.error')).PHP_EOL);
			WCF::getTPL()->clearAssign(array('exception'));
		}
		
		if ($foundDirectory) {
			self::getReader()->println(StringUtil::stripHTML(WCF::getLanguage()->getDynamicVariable('wcf.global.wcfDir.foundDirectory')).PHP_EOL);
		}
		
		$dir = self::getReader()->readLine(WCF::getLanguage()->getDynamicVariable('wcf.global.wcfDir.dir').'> ');
		if ($dir === null) exit;
		
		self::$wcfDir = StringUtil::trim($dir);
		if (self::$wcfDir === '' && $foundDirectory) {
			self::$wcfDir = $foundDirectory;
		}
		else if(self::$wcfDir === '') {
			exit;
		}
		
		$this->gotoNextStep('unzipFiles');
	}
	
	/**
	 * Shows the page for choosing the installed languages.
	 */
	protected function selectLanguages() {
		echo 'languages';
		exit;
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
		$dbNumber = 1;
		$dbClass = '';
		// set $dbClass to first item in $availableDBClasses
		foreach ($availableDBClasses as $dbClass) {
			$dbClass = $dbClass['class'];
			break;
		}
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
			
			// ensure that $dbNumber is zero or a positive integer
			if (isset($_POST['dbNumber'])) $dbNumber = max(0, intval($_POST['dbNumber']));
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
	 * @param	wcf\system\database\Database	$db
	 * @param	integer				$dbNumber
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
		
		if (!empty($matches[1])) {
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
	 * @param	string		$dir
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
		
		// rebuild language cache
		CacheHandler::getInstance()->clearResource('language');
		
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
				// we've encountered a broken archive, revert everything and then fail
				$sql = "SELECT	queueID, parentQueueID
					FROM	wcf".WCF_N."_package_installation_queue";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute();
				$queues = array();
				while ($row = $statement->fetchArray()) {
					$queues[$row['queueID']] = $row['parentQueueID'];
				}
				
				$queueIDs = array();
				$queueID = $queue->queueID;
				while ($queueID) {
					$queueIDs[] = $queueID;
					
					$queueID = (isset($queues[$queueID])) ?: 0;
				}
				
				// remove previously created queues
				if (!empty($queueIDs)) {
					$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
						WHERE		queueID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					WCF::getDB()->beginTransaction();
					foreach ($queueIDs as $queueID) {
						$statement->execute(array($queueID));
					}
					WCF::getDB()->commitTransaction();
				}
				
				// remove package files
				@unlink(TMP_DIR.'install/packages/'.$wcfPackageFile);
				foreach ($otherPackages as $packageFile) {
					@unlink(TMP_DIR.'install/packages/'.$packageFile);
				}
				
				// throw exception again
				throw new SystemException('', 0, '', $e);
			}
			
			$queue = PackageInstallationQueueEditor::create(array(
				'parentQueueID' => $queue->queueID,
				'processNo' => $processNo,
				'userID' => $admin->userID,
				'package' => $packageName,
				'packageName' => $archive->getLocalizedPackageInfo('packageName'),
				'archive' => TMP_DIR.'install/packages/'.$packageFile
			));
		}
		
		// login as admin
		$factory = new ACPSessionFactory();
		$factory->load();
		
		SessionHandler::getInstance()->changeUser($admin);
		SessionHandler::getInstance()->register('masterPassword', 1);
		SessionHandler::getInstance()->update();
		
		$installPhpDeleted = @unlink('./install.php');
		@unlink('./test.php');
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
		self::getReader()->println(str_repeat('=', self::getTerminal()->getWidth()));
		$this->setup($nextStep);
	}
}
