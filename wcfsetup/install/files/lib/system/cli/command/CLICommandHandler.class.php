<?php

namespace wcf\system\cli\command;

use phpline\internal\Log;
use wcf\system\exception\IllegalLinkException;
use wcf\system\Regex;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;

/**
 * Handles commands.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CLICommandHandler
{
    /**
     * list of all available commands
     * @var ICLICommand[]
     */
    private static $commands = [];

    /**
     * @deprecated 6.0 This method is unused and of questionable value.
     */
    public static function getCommands()
    {
        if (empty(self::$commands)) {
            $directory = DirectoryUtil::getInstance(WCF_DIR . 'lib/system/cli/command/');
            $commands = $directory->getFiles(\SORT_ASC, new Regex('Command\.class\.php$'));
            foreach ($commands as $command) {
                $class = 'wcf\system\cli\command\\' . \basename($command, '.class.php');
                if (!\class_exists($class) && !\interface_exists($class)) {
                    Log::info('Invalid command file: ', $command);
                    continue;
                }
                if (!\class_exists($class)) {
                    continue;
                }
                $object = new $class();
                if (!($object instanceof ICLICommand)) {
                    Log::info('Invalid command file: ', $command);
                    continue;
                }

                if (!$object->canAccess()) {
                    continue;
                }
                self::$commands[\strtolower(\basename($command, 'CLICommand.class.php'))] = $object;
            }
        }

        return self::$commands;
    }

    /**
     * Returns a command by the given line.
     *
     * @param string $line
     * @return  ICLICommand
     * @throws  IllegalLinkException
     */
    public static function getCommand($line)
    {
        [$command] = \explode(' ', $line . ' ', 2);

        if (!isset(self::$commands[\strtolower($command)])) {
            throw new IllegalLinkException();
        }

        return self::$commands[\strtolower($command)];
    }

    /**
     * Returns a command by the given line.
     *
     * @param string $line
     * @return  string
     * @throws  IllegalLinkException
     */
    public static function getCommandName($line)
    {
        [$command] = \explode(' ', $line . ' ', 2);

        if (!isset(self::$commands[\strtolower($command)])) {
            throw new IllegalLinkException();
        }

        return \strtolower($command);
    }

    /**
     * Returns the parameter list of the given line.
     *
     * @param string $line
     * @return  string[]
     */
    public static function getParameters($line)
    {
        [, $parameters] = \explode(' ', $line . ' ', 2);

        $chars = \str_split(StringUtil::trim($parameters));
        $tmp = '';
        $escaped = false;
        $quoted = false;
        $return = [];
        // handle quotes
        foreach ($chars as $char) {
            // escaped chars are simply added
            if ($escaped) {
                $tmp .= $char;
                $escaped = false;
            } // escaping is enabled
            elseif ($char == '\\') {
                $escaped = true;
            } // quoting is toggled
            elseif ($char == '"') {
                $quoted = !$quoted;
            } // new parameter is begun
            elseif ($char == ' ' && !$quoted) {
                $return[] = $tmp;
                $tmp = '';
            } // other chars are added
            else {
                $tmp .= $char;
            }
        }
        if ($tmp) {
            $return[] = $tmp;
        }

        return $return;
    }
}
