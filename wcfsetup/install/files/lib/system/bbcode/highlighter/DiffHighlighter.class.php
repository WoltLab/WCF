<?php

namespace wcf\system\bbcode\highlighter;

use wcf\util\StringUtil;

/**
 * Highlights difference files.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Bbcode\Highlighter
 * @deprecated  since 5.2, use Prism to highlight your code.
 */
class DiffHighlighter extends Highlighter
{
    /**
     * keywords for an added line, the + is used in unified diffs, the > in
     * normal diffs
     * @var string[]
     */
    protected $add = ["+", ">"];

    /**
     * keywords for a deleted line, the - is used in unified diff, the < in
     * normal diffs
     * @var string[]
     */
    protected $delete = ["-", "<"];

    /**
     * splitter in changes for normal diff
     * @var string[]
     */
    protected $splitter = ["---"];

    /**
     * keywords for the line info, the @ is used in unified diffs, the numbers
     * in normal diffs
     * @var string[]
     */
    protected $info = ["@", '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    /**
     * @inheritDoc
     */
    public function highlight($string)
    {
        $lines = \explode("\n", $string);
        foreach ($lines as $key => $val) {
            if (\in_array(\mb_substr($val, 0, 1), $this->info) || \in_array($val, $this->splitter)) {
                $lines[$key] = '<span class="hlComments">' . StringUtil::encodeHTML($val) . '</span>';
            } elseif (\in_array(\mb_substr($val, 0, 1), $this->add)) {
                $lines[$key] = '<span class="hlAdded">' . StringUtil::encodeHTML($val) . '</span>';
            } elseif (\in_array(\mb_substr($val, 0, 1), $this->delete)) {
                $lines[$key] = '<span class="hlRemoved">' . StringUtil::encodeHTML($val) . '</span>';
            } else {
                $lines[$key] = StringUtil::encodeHTML($val);
            }
        }

        return \implode("\n", $lines);
    }
}
