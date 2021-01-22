<?php

namespace wcf\system\bbcode\highlighter;

use wcf\system\Regex;

/**
 * Highlights syntax of TeX source code.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Bbcode\Highlighter
 * @deprecated  since 5.2, use Prism to highlight your code.
 */
class TexHighlighter extends Highlighter
{
    /**
     * @inheritDoc
     */
    protected $quotes = [];

    /**
     * @inheritDoc
     */
    protected $singleLineComment = ['%'];

    /**
     * @inheritDoc
     */
    protected function highlightKeywords($string)
    {
        $string = Regex::compile('\\$([^\\$]*)\\$', Regex::DOT_ALL)->replace($string,
            '<span class="hlKeywords2">\\0</span>');
        $string = Regex::compile('(\\\\(?:[a-z]+))(\\[[^\\]\\\\]+\\])?(\\{[^\\}]*\\})?',
            Regex::CASE_INSENSITIVE)->replace($string,
            '<span class="hlKeywords3">\\1</span><span class="hlKeywords4">\\2</span><span class="hlKeywords1">\\3</span>');

        return \str_replace('\\\\', '<span class="hlKeywords3">\\\\</span>', $string);
    }

    /**
     * @inheritDoc
     */
    protected function highlightNumbers($string)
    {
        // do not highlight numbers
        return $string;
    }
}
