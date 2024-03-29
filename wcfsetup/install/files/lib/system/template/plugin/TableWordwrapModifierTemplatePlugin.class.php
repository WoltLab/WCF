<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;

/**
 * Template modifier plugin which wordwraps a string.
 *
 * Usage:
 *  {$foo|tableWordwrap}
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TableWordwrapModifierTemplatePlugin implements IModifierTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        // values
        $width = 30;
        // zero width space
        $break = "\xE2\x80\x8B";
        $string = $tagArgs[0];

        if ($string === null) {
            return '';
        }

        $result = '';
        $substrings = \explode(' ', $string);

        foreach ($substrings as $substring) {
            if ($result !== '') {
                $result .= ' ';
            }

            $length = \mb_strlen($substring);
            if ($length > $width) {
                $j = \ceil($length / $width);

                for ($i = 0; $i < $j; $i++) {
                    if ($i) {
                        $result .= $break;
                    }
                    if ($width * ($i + 1) > $length) {
                        $result .= \mb_substr($substring, $width * $i);
                    } else {
                        $result .= \mb_substr($substring, $width * $i, $width);
                    }
                }
            } else {
                $result .= $substring;
            }
        }

        return $result;
    }
}
