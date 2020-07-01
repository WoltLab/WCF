<?php
namespace wcf\system;
use phpline\console\ConsoleReader;
use phpline\internal\Log;
use phpline\TerminalFactory;
use wcf\data\session\SessionEditor;
use wcf\system\cli\command\CLICommandHandler;
use wcf\system\cli\command\CLICommandNameCompleter;
use wcf\system\cli\DatabaseCLICommandHistory;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\util\CLIUtil;
use wcf\util\FileUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;
use Zend\Loader\StandardAutoloader as ZendLoader;

// set exception handler
set_exception_handler([CLIWCF::class, 'handleCLIException']);

/**
 * Extends WCF class with functions for CLI.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System
 */
class CLIWCF extends WCF {
	/**
	 * instance of ConsoleReader
	 * @var	ConsoleReader
	 */
	protected static $consoleReader = null;
	
	/**
	 * instance of ArgvParser
	 * @var	\Zend\Console\Getopt
	 */
	protected static $argvParser = null;
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * Calls all init functions of the WCF class.
	 */
	public function __construct() {
		// add autoload directory
		self::$autoloadDirectories['wcf'] = WCF_DIR . 'lib/';
		
		// define tmp directory
		if (!defined('TMP_DIR')) define('TMP_DIR', FileUtil::getTempFolder());
		
		// register additional autoloaders
		require_once(WCF_DIR.'lib/system/api/phpline/phpline.phar');
		require_once(WCF_DIR.'lib/system/api/zend/Loader/StandardAutoloader.php');
		$zendLoader = new ZendLoader([ZendLoader::AUTOREGISTER_ZF => true]);
		$zendLoader->register();
		
		$argv = new ArgvParser([
			'packageID=i' => ''
		]);
		$argv->setOption(ArgvParser::CONFIG_FREEFORM_FLAGS, true);
		$argv->parse();
		define('PACKAGE_ID', $argv->packageID ?: 1);
		
		// disable benchmark
		define('ENABLE_BENCHMARK', 0);
		
		// start initialization
		$this->initDB();
		$this->loadOptions();
		$this->initSession();
		$this->initLanguage();
		$this->initTPL();
		$this->initCoreObjects();
		$this->initApplications();
		
		// the destructor registered in core.functions.php will only call the destructor of the parent class
		register_shutdown_function([self::class, 'destruct']);
		
		$this->initArgv();
		$this->initPHPLine();
		$this->initAuth(self::getArgvParser()->sessionID);
		$this->checkForUpdates();
		$this->initCommands();
	}
	
	/**
	 * @inheritDoc
	 */
	public static function destruct() {
		// Giving a sessionID disables saving of the command history.
		if (!self::getArgvParser()->sessionID) {
			if (self::getReader() !== null && self::getReader()->getHistory() instanceof DatabaseCLICommandHistory) {
				/** @var DatabaseCLICommandHistory $history */
				$history = self::getReader()->getHistory();
				
				$history->save();
				$history->autoSave = false;
			}
		}
		
		if (!self::getArgvParser()->sessionID) {
			self::getSession()->delete();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static final function handleCLIException($e) {
		die($e->getMessage()."\n".$e->getTraceAsString());
	}
	
	/**
	 * Initializes parsing of command line options.
	 */
	protected function initArgv() {
		// initialise ArgvParser
		self::$argvParser = new ArgvParser([
			'language=s' => WCF::getLanguage()->get('wcf.cli.help.language'),
			'v' => WCF::getLanguage()->get('wcf.cli.help.v'),
			'q' => WCF::getLanguage()->get('wcf.cli.help.q'),
			'h|help-s' => WCF::getLanguage()->get('wcf.cli.help.help'),
			'version' => WCF::getLanguage()->get('wcf.cli.help.version'),
			'disableUpdateCheck' => WCF::getLanguage()->get('wcf.cli.help.disableUpdateCheck'),
			'exitOnFail' => WCF::getLanguage()->get('wcf.cli.help.exitOnFail'),
			'packageID=i' => WCF::getLanguage()->get('wcf.cli.help.packageID'),
			'sessionID=s' => 'sessionid',
		]);
		self::getArgvParser()->setOptions([
			ArgvParser::CONFIG_CUMULATIVE_FLAGS => true,
			ArgvParser::CONFIG_DASHDASH => false
		]);
		
		// parse arguments
		EventHandler::getInstance()->fireAction($this, 'beforeArgumentParsing');
		try {
			self::getArgvParser()->parse();
		}
		catch (ArgvException $e) {
			// show error message and usage
			echo $e->getMessage().PHP_EOL;
			echo self::getArgvParser()->getUsageMessage();
			exit;
		}
		EventHandler::getInstance()->fireAction($this, 'afterArgumentParsing');
		
		// handle arguments
		if (self::getArgvParser()->help === true) {
			// show usage
			echo self::getArgvParser()->getUsageMessage();
			exit;
		}
		else if (self::getArgvParser()->help) {
			$help = WCF::getLanguage()->get('wcf.cli.help.'.self::getArgvParser()->help.'.description', true);
			if ($help) echo $help.PHP_EOL;
			else {
				echo WCF::getLanguage()->getDynamicVariable('wcf.cli.help.noLongHelp', ['topic' => self::getArgvParser()->help]).PHP_EOL;
			}
			exit;
		}
		if (self::getArgvParser()->version) {
			// show version
			echo WCF_VERSION.PHP_EOL;
			exit;
		}
		if (self::getArgvParser()->language) {
			// set language
			$language = LanguageFactory::getInstance()->getLanguageByCode(self::getArgvParser()->language);
			if ($language === null) {
				echo WCF::getLanguage()->getDynamicVariable('wcf.cli.error.language.notFound', ['languageCode' => self::getArgvParser()->language]).PHP_EOL;
				exit;
			}
			self::setLanguage($language->languageID);
		}
		if (in_array('moo', self::getArgvParser()->getRemainingArgs())) {
			echo '...."Have you mooed today?"...'.PHP_EOL;
		}
		
		define('VERBOSITY', self::getArgvParser()->v - self::getArgvParser()->q);
	}
	
	/**
	 * Returns the argv parser.
	 * 
	 * @return	\Zend\Console\Getopt
	 */
	public static function getArgvParser() {
		return self::$argvParser;
	}
	
	/**
	 * Initializes PHPLine.
	 */
	protected function initPHPLine() {
		$terminal = TerminalFactory::get();
		self::$consoleReader = new ConsoleReader("WoltLab Suite", null, null, $terminal);
		
		// don't expand events, as the username and password will follow
		self::getReader()->setExpandEvents(false);
		
		if (VERBOSITY >= 0) {
			$headline = str_pad("WoltLab Suite (tm) ".WCF_VERSION, self::getTerminal()->getWidth(), " ", STR_PAD_BOTH);
			self::getReader()->println($headline);
		}
	}
	
	/**
	 * Returns ConsoleReader.
	 * 
	 * @return	ConsoleReader
	 */
	public static function getReader() {
		return self::$consoleReader;
	}
	
	/**
	 * Returns the terminal that is attached to ConsoleReader
	 * 
	 * @return	\phpline\Terminal
	 */
	public static function getTerminal() {
		return self::getReader()->getTerminal();
	}
	
	/**
	 * Does the user authentification.
	 */
	protected function initAuth($sessionID = null) {
		if ($sessionID !== null) {
			self::getSession()->delete();
			self::getSession()->load(SessionEditor::class, $sessionID);
			if (!self::getUser()->userID) {
				self::getReader()->println('Invalid sessionID');
				exit(1);
			}
		}
		else {
			do {
				$line = self::getReader()->readLine(WCF::getLanguage()->get('wcf.user.username').'> ');
				if ($line === null) exit;
				$username = StringUtil::trim($line);
			}
			while ($username === '');
			
			do {
				$line = self::getReader()->readLine(WCF::getLanguage()->get('wcf.user.password').'> ', '*');
				if ($line === null) exit;
				$password = StringUtil::trim($line);
			}
			while ($password === '');
			
			// check credentials and switch user
			try {
				$user = UserAuthenticationFactory::getInstance()->getUserAuthentication()->loginManually($username, $password);
				WCF::getSession()->changeUser($user);
			}
			catch (UserInputException $e) {
				$message = WCF::getLanguage()->getDynamicVariable('wcf.user.'.$e->getField().'.error.'.$e->getType(), ['username' => $username]);
				self::getReader()->println($message);
				exit(1);
			}
		}
		
		// initialize history
		$history = new DatabaseCLICommandHistory();
		$history->load();
		self::getReader()->setHistory($history);
		
		// initialize language
		if (!self::getArgvParser()->language) $this->initLanguage();
	}
	
	/**
	 * Initializes command handling.
	 */
	protected function initCommands() {
		// add command name completer
		self::getReader()->addCompleter(new CLICommandNameCompleter());
		
		while (true) {
			// roll back open transactions of the previous command, as they are dangerous in a long living script
			if (WCF::getDB()->rollBackTransaction()) {
				Log::warn('Previous command had an open transaction.');
			}
			self::getReader()->setHistoryEnabled(true);
			$line = self::getReader()->readLine('>');
			if ($line === null) exit;
			$line = StringUtil::trim($line);
			try {
				$command = CLICommandHandler::getCommand($line);
				$command->execute(CLICommandHandler::getParameters($line));
			}
			catch (IllegalLinkException $e) {
				Log::error('notFound:'.JSON::encode(['command' => $line]));
				self::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.cli.error.command.notFound', ['command' => $line]));
				
				if (self::getArgvParser()->exitOnFail) {
					exit(1);
				}
				continue;
			}
			catch (PermissionDeniedException $e) {
				Log::error('permissionDenied');
				self::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.page.error.permissionDenied'));
				
				if (self::getArgvParser()->exitOnFail) {
					exit(1);
				}
				continue;
			}
			catch (ArgvException $e) {
				// show error message and usage
				if ($e->getMessage()) echo $e->getMessage().PHP_EOL;
				echo $e->getUsageMessage();
				
				if (self::getArgvParser()->exitOnFail) {
					exit(1);
				}
				continue;
			}
			catch (\Exception $e) {
				Log::error($e);
				
				if (self::getArgvParser()->exitOnFail) {
					exit(1);
				}
				continue;
			}
		}
	}
	
	/**
	 * Checks for updates.
	 * 
	 * @return	string
	 */
	public function checkForUpdates() {
		if (WCF::getSession()->getPermission('admin.configuration.package.canUpdatePackage') && VERBOSITY >= -1 && !self::getArgvParser()->disableUpdateCheck) {
			$updates = PackageUpdateDispatcher::getInstance()->getAvailableUpdates();
			if (!empty($updates)) {
				self::getReader()->println(count($updates) . ' update' . (count($updates) > 1 ? 's are' : ' is') . ' available');
				
				if (VERBOSITY >= 1) {
					$table = [
						[
							WCF::getLanguage()->get('wcf.acp.package.name'),
							WCF::getLanguage()->get('wcf.acp.package.version'),
							WCF::getLanguage()->get('wcf.acp.package.newVersion')
						]
					];
					
					foreach ($updates as $update) {
						$row = [
							WCF::getLanguage()->get($update['packageName']),
							$update['packageVersion'],
							$update['version']['packageVersion']
						];
						
						$table[] = $row;
					}
					
					self::getReader()->println(CLIUtil::generateTable($table));
				}
			}
		}
	}
}
