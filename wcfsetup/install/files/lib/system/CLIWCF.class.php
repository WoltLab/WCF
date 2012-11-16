<?php
namespace wcf\system;
use phpline\console\ConsoleReader;
use phpline\internal\Log;
use phpline\TerminalFactory;
use wcf\system\cli\command\CommandHandler;
use wcf\system\cli\command\CommandNameCompleter;
use wcf\system\cli\DatabaseCommandHistory;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\util\StringUtil;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;
use Zend\Loader\StandardAutoloader as ZendLoader;

/**
 * Extends WCF class with functions for CLI.
 *
 * @author	Tim Düsterhus
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category	Community Framework
 */
class CLIWCF extends WCF {
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
	 * Calls all init functions of the WCF class.
	 */
	public function __construct() {
		parent::__construct();
		
		// the destructor registered in core.functions.php will only call the destructor of the parent class
		register_shutdown_function(array('wcf\system\CLIWCF', 'destruct'));
		
		// register additional autoloaders
		require_once(WCF_DIR.'lib/system/api/phpline/phpline.phar');
		require_once(WCF_DIR.'lib/system/api/zend/Loader/StandardAutoloader.php');
		$zendLoader = new ZendLoader(array(ZendLoader::AUTOREGISTER_ZF => true));
		$zendLoader->register();
		
		$this->initArgv();
		$this->initPHPLine();
		$this->initAuth();
		// TODO: Show whether there are updates available (similar to TTYs at Ubuntu Linux)
		$this->initCommands();
	}
	
	/**
	 * Destroys the session.
	 * 
	 * @see wcf\system\WCF::destruct()
	 */
	public static function destruct() {
		self::getSession()->delete();
	}
	
	/**
	 * Initializes parsing of command line options.
	 */
	protected function initArgv() {
		self::$argvParser = new ArgvParser(array(
			'language=s' => 'Sets the language to the specified languagecode and ignores user settings',
			'v' => 'verbose: show more output',
			'q' => 'quiet: show less output',
			'h|help' => 'show this help',
			'version' => 'show version'
		));
		self::getArgvParser()->setOptions(array(
			ArgvParser::CONFIG_CUMULATIVE_FLAGS => true,
			ArgvParser::CONFIG_DASHDASH => false
		));
		
		// parse arguments
		EventHandler::getInstance()->fireAction($this, 'beforeArgumentParsing');
		try {
			self::getArgvParser()->parse();
		}
		catch (ArgvException $e) {
			echo $e->getMessage()."\n";
			echo self::getArgvParser()->getUsageMessage();
			exit;
		}
		EventHandler::getInstance()->fireAction($this, 'afterArgumentParsing');
		
		// handle arguments
		if (self::getArgvParser()->help) {
			echo self::getArgvParser()->getUsageMessage();
			exit;
		}
		if (self::getArgvParser()->version) {
			echo WCF_VERSION."\n";
			exit;
		}
		if (self::getArgvParser()->language) {
			$language = LanguageFactory::getInstance()->getLanguageByCode(self::getArgvParser()->language);
			if ($language === null) {
				echo "Could not find language with code '".self::getArgvParser()->language."'\n";
				exit;
			}
			self::setLanguage($language->languageID);
		}
		if (in_array('moo', self::getArgvParser()->getRemainingArgs())) {
			echo '...."Have you mooed today?"...'."\n";
		}
		
		define('VERBOSITY', self::getArgvParser()->v - self::getArgvParser()->q);
		if (VERBOSITY > 1) Log::enableDebug();
		if (VERBOSITY > 2) Log::enableTrace();
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
		
		$headline = str_pad("WoltLab (r) Community Framework (tm) ".WCF_VERSION, self::getTerminal()->getWidth(), " ", STR_PAD_BOTH);
		self::getReader()->println($headline);
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
	 * Converts certain HTML entities to a proper CLI counterpart.
	 * 
	 * @param	string	$string
	 * @return	string
	 */
	public static function convertEntities($string) {
		return Regex::compile('&[lrb]dquo;')->replace($string, '"');
	}
	
	/**
	 * Does the user authentification.
	 */
	protected function initAuth() {
		do {
			$username = StringUtil::trim(self::getReader()->readLine(WCF::getLanguage()->get('wcf.user.username').'> '));
		}
		while ($username === '');
		do {
			$password = StringUtil::trim(self::getReader()->readLine(WCF::getLanguage()->get('wcf.user.password').'> ', '*'));
		}
		while ($password === '');
		
		try {
			$user = UserAuthenticationFactory::getUserAuthentication()->loginManually($username, $password);
			WCF::getSession()->changeUser($user);
		}
		catch (UserInputException $e) {
			$message = WCF::getLanguage()->getDynamicVariable('wcf.user.'.$e->getField().'.error.'.$e->getType(), array('username' => $username));
			self::getReader()->println(self::convertEntities($message));
			exit;
		}
		
		// initialize history
		$history = new DatabaseCommandHistory();
		$history->load();
		self::getReader()->setHistory($history);
		
		// initialize language
		if (!self::getArgvParser()->language) $this->initLanguage();
	}
	
	/**
	 * Initializes command handling.
	 */
	protected function initCommands() {
		self::getReader()->addCompleter(new CommandNameCompleter());
		while (true) {
			// roll back open transactions of the previous command, as they are dangerous in a long living script
			if (WCF::getDB()->rollBackTransaction()) {
				Log::warn('Previous command had an open transaction.');
			}
			$line = StringUtil::trim(self::getReader()->readLine('>'));
			try {
				$command = CommandHandler::getCommand($line);
				$command->execute(CommandHandler::getParameters($line));
			}
			catch (IllegalLinkException $e) {
				self::getReader()->println("Command not found: ".$line);
				continue;
			}
			catch (ArgvException $e) {
				echo $e->getMessage()."\n";
				echo str_replace($_SERVER['argv'][0], CommandHandler::getCommandName($line), $e->getUsageMessage());
				continue;
			}
		}
	}
	
	/**
	 * Generates a table.
	 */
	public static function generateTable(array $table) {
		$columnSize = array();
		foreach ($table as $row) {
			$i = 0;
			foreach ($row as $column) {
				if (!isset($columnSize[$i])) $columnSize[$i] = 0;
				$columnSize[$i] = max($columnSize[$i], StringUtil::length($column));
				$i++;
			}
		}
		
		$result = '';
		$result .= '+';
		foreach ($columnSize as $column) {
			$result .= str_repeat('-', $column + 2).'+';
		}
		$result .= "\n";
		
		foreach ($table as $row) {
			$result .= "|";
			$i = 0;
			foreach ($row as $column) {
				$result .= ' '.StringUtil::pad($column, $columnSize[$i], ' ', (is_numeric($column) ? STR_PAD_LEFT : STR_PAD_RIGHT)).' |';
				$i++;
			}
			
			$result .= "\n+";
			foreach ($columnSize as $column) {
				$result .= str_repeat('-', $column + 2).'+';
			}
			$result .= "\n";
		}
		
		return $result;
	}
}
