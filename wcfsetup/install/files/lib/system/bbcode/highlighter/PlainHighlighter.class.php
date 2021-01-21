<?php

namespace wcf\system\bbcode\highlighter;

use wcf\util\StringUtil;

/**
 * Does no highlighting.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Bbcode\Highlighter
 * @deprecated  since 5.2, use Prism to highlight your code.
 */
class PlainHighlighter extends Highlighter
{
    /**
     * @inheritDoc
     */
    public function highlight($code)
    {
        return StringUtil::encodeHTML($code);
    }
}
