<?php
namespace wcf\system\cli\command;
use wcf\system\CLIWCF;
use wcf\util\CLIUtil;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;

/**
 * Shows command usage.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class HelpCLICommand implements IArgumentedCLICommand {
	/**
	 * arguments parser
	 * @var	\Zend\Console\Getopt
	 */
	protected $argv = null;
		
	/**
	 * Initializes the argument parser.
	 */
	public function __construct() {
		$this->argv = new ArgvParser(array());
		$this->argv->setOptions(array(
			ArgvParser::CONFIG_FREEFORM_FLAGS => true,
			ArgvParser::CONFIG_PARSEALL => false,
			ArgvParser::CONFIG_CUMULATIVE_PARAMETERS => true
		));
	}
	
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::execute()
	 */
	public function execute(array $parameters) {
		$this->argv->setArguments($parameters);
		$this->argv->parse();
		
		$args = $this->argv->getRemainingArgs();
		// validate parameters
		if (count($args) != 1) {
			throw new ArgvException('', $this->getUsage());
		}
		$commands = CLICommandHandler::getCommands();
		
		if (!isset($commands[$args[0]])) {
			throw new ArgvException(CLIWCF::getLanguage()->getDynamicVariable('wcf.cli.error.command.notFound', array('command' => $args[0])), $this->getUsage());
		}
		
		$command = $commands[$args[0]];
		if (!($command instanceof IArgumentedCLICommand)) {
			throw new ArgvException(CLIWCF::getLanguage()->getDynamicVariable('wcf.cli.error.help.noArguments', array('command' => $args[0])), $this->getUsage());
		}
		
		CLIWCF::getReader()->println($command->getUsage());
	}
	
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::getUsage()
	 */
	public function getUsage() {
		return str_replace($_SERVER['argv'][0].' [ options ]', 'help [ options ] <command>', $this->argv->getUsageMessage());
	}
	
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::canAccess()
	 */
	public function canAccess() {
		return true;
	}
}
