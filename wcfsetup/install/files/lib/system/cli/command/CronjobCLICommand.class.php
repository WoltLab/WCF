<?php
namespace wcf\system\cli\command;
use wcf\system\cronjob\CronjobScheduler;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;

/**
 * Executes cronjobs.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class CronjobCLICommand implements ICLICommand {
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::execute()
	 */
	public function execute(array $parameters) {
		$argv = new ArgvParser(array());
		$argv->setArguments($parameters);
		$argv->parse();
		
		$args = $argv->getRemainingArgs();
		if (count($args) != 1 || $args[0] != 'execute') {
			throw new ArgvException('', $this->fixUsage($argv->getUsageMessage()));
		}
		
		CronjobScheduler::getInstance()->executeCronjobs();
	}
	
	/**
	 * Returns fixed usage message of ArgvParser.
	 * 
	 * @param	string		$usage
	 * @return	string
	 */
	public function fixUsage($usage) {
		return str_replace($_SERVER['argv'][0].' [ options ]', $_SERVER['argv'][0].' [ options ] execute', $usage);
	}
	
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::canAccess()
	 */
	public function canAccess() {
		return true;
	}
}
