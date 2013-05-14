<?php
namespace wcf\system\cli\command;
use wcf\system\cronjob\CronjobScheduler;
use wcf\system\CLIWCF;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;

/**
 * Executes cronjobs.
 *
 * @author	Tim Düsterhus
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class CronjobCommand implements ICommand {
	/**
	 * @see \wcf\system\cli\command\ICommand::execute()
	 */
	public function execute(array $parameters) {
		$argv = new ArgvParser(array());
		$argv->setArguments($parameters);
		$argv->parse();
		
		$args = $argv->getRemainingArgs();
		if (count($args) != 1 || $args[0] != 'execute') {
			throw new ArgvException('', $this->fixUsage($argv->getUsageMessage()));
		}
		
		// TODO: As applications are not loaded application specific cronjobs cannot be executed
		CronjobScheduler::getInstance()->executeCronjobs();
	}
	
	public function fixUsage($usage) {
		return str_replace($_SERVER['argv'][0].' [ options ]', $_SERVER['argv'][0].' [ options ] execute', $usage);
	}
	
	/**
	 * @see \wcf\system\cli\command\ICommand::canAccess()
	 */
	public function canAccess() {
		return true;
	}
}
