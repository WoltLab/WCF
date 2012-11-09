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
		
		// register additional autoloaders
		require_once(WCF_DIR.'lib/system/api/phpline/phpline.phar');
		require_once(WCF_DIR.'lib/system/api/zend/Loader/StandardAutoloader.php');
		$zendLoader = new ZendLoader(array(ZendLoader::AUTOREGISTER_ZF => true));
		$zendLoader->register();
		
		$this->initArgv();
		$this->initPHPLine();
		$this->initAuth();
	}
	
	/**
	 * Initializes parsing of command line options.
	 */
	protected function initArgv() {
		self::$argvParser = new ArgvParser(array(
			'v' => 'Verbose: Show more output',
			'q' => 'Quiet: Show less output',
			'h|help' => 'Show this help'
		));
		self::getArgvParser()->setOptions(array(ArgvParser::CONFIG_CUMULATIVE_FLAGS => true));
		
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
		
		if (self::getArgvParser()->getOption('help')) {
			echo self::getArgvParser()->getUsageMessage();
			exit;
		}
		if (in_array('moo', self::getArgvParser()->getRemainingArgs())) {
			echo '...."Have you mooed today?"...'."\n";
		}
		
		define('VERBOSITY', self::getArgvParser()->getOption('v') - self::getArgvParser()->getOption('q'));
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
	public function getReader() {
		return self::$consoleReader;
	}
	
	/**
	 * Returns the terminal that is attached to ConsoleReader
	 * 
	 * @return phpline\Terminal
	 */
	public function getTerminal() {
		return self::getReader()->getTerminal();
	}
	
	/**
	 * Converts certain HTML entities to a proper CLI counterpart.
	 * 
	 * @param	string	$string
	 * @return	string
	 */
	public function convertEntities($string) {
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
		
		$history = new DatabaseCommandHistory();
		$history->load();
		self::getReader()->setHistory($history);
		
		self::getReader()->addCompleter(new CommandNameCompleter());
		while (true) {
			$line = StringUtil::trim(self::getReader()->readLine('>'));
			try {
				$command = CommandHandler::getCommand($line);
				$command->execute();
			}
			catch (IllegalLinkException $e) {
				self::getReader()->println("Command not found: ".$line);
				continue;
			}
		}
	}
}
