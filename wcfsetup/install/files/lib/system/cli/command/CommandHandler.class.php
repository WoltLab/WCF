<?php
namespace wcf\system\cli\command;
use wcf\system\exception\IllegalLinkException;
use wcf\system\Regex;
use wcf\util\DirectoryUtil;
use phpline\internal\Log;

/**
 * Handles commands.
 *
 * @author	Tim Düsterhus
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class CommandHandler {
	/**
	 * all available commands
	 * 
	 * @var array<wcf\system\cli\command\ICommand>
	 */
	private static $commands = array();
	
	/**
	 * Retuns all available commands.
	 * 
	 * @return array<wcf\system\cli\command\ICommand>
	 */
	public static function getCommands() {
		if (empty(self::$commands)) {
			$directory = DirectoryUtil::getInstance(WCF_DIR.'lib/system/cli/command/');
			$commands = $directory->getFiles(SORT_ASC, new Regex('Command\.class\.php$'));
			foreach ($commands as $command) {
				$class = 'wcf\system\cli\command\\'.basename($command, '.class.php');
				if (!class_exists($class) && !interface_exists($class)) {
					Log::info('Invalid command file: ', $command);
					continue;
				}
				if (!class_exists($class)) continue;
				$object = new $class();
				if (!($object instanceof ICommand)) {
					Log::info('Invalid command file: ', $command);
					continue;
				}
				
				if (!$object->canAccess()) continue;
				self::$commands[strtolower(basename($command, 'Command.class.php'))] = $object;
			}
		}
		
		return self::$commands;
	}
	
	/**
	 * Returns a command by the given line.
	 * 
	 * @param	string	$line
	 * @return	wcf\system\cli\command\ICommand
	 */
	public static function getCommand($line) {
		list($command, $parameters) = explode(' ', $line.' ', 2);
		
		if (!isset(self::$commands[strtolower($command)])) throw new IllegalLinkException();
		
		return self::$commands[strtolower($command)];
	}
	
	/**
	 * Returns a command by the given line.
	 *
	 * @param	string	$line
	 * @return	string
	 */
	public static function getCommandName($line) {
		list($command, $parameters) = explode(' ', $line.' ', 2);
	
		if (!isset(self::$commands[strtolower($command)])) throw new IllegalLinkException();
	
		return strtolower($command);
	}
	
	/**
	 * Returns the parameterlist of the given line.
	 * 
	 * @param	string	$line
	 * @return	array<string>
	 */
	public static function getParameters($line) {
		$parameters = explode(' ', $line);
		array_shift($parameters);
		
		return $parameters;
	}
}
