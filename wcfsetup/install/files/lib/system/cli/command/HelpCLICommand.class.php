<?php
namespace wcf\system\cli\command;
use wcf\system\CLIWCF;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;

/**
 * Shows command usage.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cli\Command
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
		$this->argv = new ArgvParser([]);
		$this->argv->setOptions([
			ArgvParser::CONFIG_FREEFORM_FLAGS => true,
			ArgvParser::CONFIG_PARSEALL => false,
			ArgvParser::CONFIG_CUMULATIVE_PARAMETERS => true
		]);
	}
	
	/**
	 * @inheritDoc
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
			throw new ArgvException(CLIWCF::getLanguage()->getDynamicVariable('wcf.cli.error.command.notFound', ['command' => $args[0]]), $this->getUsage());
		}
		
		$command = $commands[$args[0]];
		if (!($command instanceof IArgumentedCLICommand)) {
			throw new ArgvException(CLIWCF::getLanguage()->getDynamicVariable('wcf.cli.error.help.noArguments', ['command' => $args[0]]), $this->getUsage());
		}
		
		CLIWCF::getReader()->println($command->getUsage());
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUsage() {
		return str_replace($_SERVER['argv'][0].' [ options ]', 'help [ options ] <command>', $this->argv->getUsageMessage());
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAccess() {
		return true;
	}
}
