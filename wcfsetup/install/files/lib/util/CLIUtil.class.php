<?php

namespace wcf\util;

use phpline\internal\AnsiUtil;
use wcf\system\CLIWCF;

/**
 * Provide convenience methods for use on command line interface.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CLIUtil
{
    /**
     * Generates a table.
     *
     * @param string[][] $table
     */
    public static function generateTable(array $table): string
    {
        $columnSize = [];
        foreach ($table as $row) {
            $i = 0;
            foreach ($row as $column) {
                if (!isset($columnSize[$i])) {
                    $columnSize[$i] = 0;
                }
                $columnSize[$i] = \max($columnSize[$i], \mb_strlen(AnsiUtil::stripAnsi($column)));
                $i++;
            }
        }

        $result = '';
        $result .= '+';
        foreach ($columnSize as $column) {
            $result .= \str_repeat('-', $column + 2) . '+';
        }
        $result .= \PHP_EOL;

        foreach ($table as $row) {
            $result .= "|";
            $i = 0;
            foreach ($row as $column) {
                $paddedString = StringUtil::pad(
                    AnsiUtil::stripAnsi($column),
                    $columnSize[$i],
                    ' ',
                    (\is_numeric($column) ? \STR_PAD_LEFT : \STR_PAD_RIGHT)
                );
                $result .= ' ' . \str_replace(AnsiUtil::stripAnsi($column), $column, $paddedString) . ' |';
                $i++;
            }

            $result .= \PHP_EOL . "+";
            foreach ($columnSize as $column) {
                $result .= \str_repeat('-', $column + 2) . '+';
            }
            $result .= \PHP_EOL;
        }

        return $result;
    }

    /**
     * Generates a list.
     *
     * @param string[] $list
     */
    public static function generateList(array $list): string
    {
        $result = '';
        foreach ($list as $row) {
            $parts = \mb_str_split($row, CLIWCF::getTerminal()->getWidth() - 2);
            $result .= '* ' . \implode(\PHP_EOL . '  ', $parts) . \PHP_EOL;
        }

        return $result;
    }

    /**
     * Forbid creation of CLIUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
