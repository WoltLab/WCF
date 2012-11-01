<?php
namespace wcf\system;
require_once('WCF.class.php');
use phpline\console\ConsoleReader;
use phpline\TerminalFactory;
use wcf\system\cli\DatabaseCommandHistory;
use wcf\system\exception\UserInputException;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\util\StringUtil;
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
	 * Calls all init functions of the WCF class.
	 */
	public function __construct() {
		parent::__construct();
		
		// register additional autoloaders
		require_once(WCF_DIR.'lib/system/api/phpline/phpline.phar');
		require_once(WCF_DIR.'lib/system/api/zend/Loader/StandardAutoloader.php');
		$zendLoader = new ZendLoader(array(ZendLoader::AUTOREGISTER_ZF => true));
		$zendLoader->register();
		
		$this->initPHPLine();
		$this->initAuth();
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
		
		while ('exit' !== StringUtil::trim(self::getReader()->readLine('>')));
	}
}
