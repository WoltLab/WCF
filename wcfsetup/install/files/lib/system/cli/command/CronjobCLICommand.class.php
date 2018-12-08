<?php
namespace wcf\system\cli\command;
use wcf\data\user\User;
use wcf\system\cronjob\CronjobScheduler;
use wcf\system\WCF;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;

/**
 * Executes cronjobs.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cli\Command
 */
class CronjobCLICommand implements IArgumentedCLICommand {
	/**
	 * arguments parser
	 * @var	\Zend\Console\Getopt
	 */
	protected $argv = null;
	
	/**
	 * Initializes the argument parser.
	 */
	public function __construct() {
		$this->argv = new ArgvParser([]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $parameters) {
		$this->argv->setArguments($parameters);
		$this->argv->parse();
		
		$args = $this->argv->getRemainingArgs();
		if (count($args) != 1 || $args[0] != 'execute') {
			throw new ArgvException('', $this->getUsage());
		}
		
		// switch session owner to 'system' during execution of cronjobs
		$actualUser = WCF::getUser();
		WCF::getSession()->changeUser(new User(null, ['userID' => 0, 'username' => 'System']), true);
		WCF::getSession()->disableUpdate();
		
		try {
			CronjobScheduler::getInstance()->executeCronjobs();
		}
		finally {
			// switch session back to the actual user 
			WCF::getSession()->changeUser($actualUser, true);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUsage() {
		return str_replace($_SERVER['argv'][0].' [ options ]', 'cronjob [ options ] execute', $this->argv->getUsageMessage());
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAccess() {
		return true;
	}
}
