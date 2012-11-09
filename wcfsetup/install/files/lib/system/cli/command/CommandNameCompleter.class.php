<?php
namespace wcf\system\cli\command;
use phpline\console\completer\Completer;

/**
 * Completes commands.
 *
 * @author	Tim Düsterhus
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class CommandNameCompleter implements Completer {
	/**
	 * list of available commands
	 * @var array<string>
	 */
	private $commands = array();
	
	/**
	 * Reads available commands.
	 */
	public function __construct() {
		$this->commands = array_keys(CommandHandler::getCommands());
	}
	
	/**
	 * @see \phpline\console\completer\Completer::complete()
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
