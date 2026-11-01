<?php

namespace wcf\util;

/**
 * Contains Style-related functions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class StyleUtil
{
    /**
     * Compresses css code.
     */
    public static function compressCSS(string $string): string
    {
        $string = StringUtil::unifyNewlines($string);

        // remove comments
        $string = \preg_replace('!/\*.*?\*/\r?\n?!s', '', $string);
        // remove tabs
        $string = \preg_replace('!\t+!', '', $string);
        // remove line breaks
        $string = \preg_replace('!(?<=\{|;) *\n!', '', $string);
        $string = \preg_replace('! *\n(?=})!', '', $string);
        // remove empty lines
        $string = \preg_replace('~\n{2,}~s', "\n", $string);

        return StringUtil::trim($string);
    }

    /**
     * Forbid creation of StyleUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
