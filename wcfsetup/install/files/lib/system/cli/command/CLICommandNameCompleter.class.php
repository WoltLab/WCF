<?php
namespace wcf\system\cli\command;
use phpline\console\completer\Completer;

/**
 * Completes commands.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cli\Command
 */
class CLICommandNameCompleter implements Completer {
	/**
	 * names of all available commands
	 * @var	string[]
	 */
	private $commands = [];
	
	/**
	 * Creates a new instance of CLICommandNameCompleter.
	 */
	public function __construct() {
		$this->commands = array_keys(CLICommandHandler::getCommands());
	}
	
	/**
	 * @inheritDoc
	 */
	public function complete($buffer, $cursor, array &$candidates) {
		if ($buffer === null) {
			foreach ($this->commands as $command) $candidates[] = $command;
		}
		else {
			foreach ($this->commands as $command) {
				if (stripos($command, $buffer) === 0) {
					$candidates[] = $command;
				}
			}
		}
		
		if (count($candidates) == 1) {
			$candidates[0] = $candidates[0]." ";
		}
		sort($candidates);
		return empty($candidates) ? -1 : 0;
	}
}
