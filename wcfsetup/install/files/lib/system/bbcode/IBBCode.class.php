<?php

namespace wcf\system\bbcode;

use wcf\data\IDatabaseObjectProcessor;

/**
 * Any special bbcode class should implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IBBCode extends IDatabaseObjectProcessor
{
    /**
     * Returns the parsed bbcode tag.
     *
     * @param string $content
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser): string;
}
