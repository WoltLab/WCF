<?php

namespace wcf\system\bbcode;

use wcf\util\StringUtil;

/**
 * Parses the [email] bbcode tag.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class EmailBBCode extends AbstractBBCode
{
    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser): string
    {
        $email = '';
        if (isset($openingTag['attributes'][0])) {
            $email = $openingTag['attributes'][0];
        }
        $email = StringUtil::decodeHTML($email);

        return '<a href="mailto:' . StringUtil::encodeAllChars($email) . '">' . StringUtil::encodeHTML($email) . '</a>';
    }
}
