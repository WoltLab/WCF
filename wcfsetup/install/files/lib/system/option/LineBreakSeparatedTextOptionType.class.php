<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Option type implementation for separate items that are stored as line break-separated text.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
class LineBreakSeparatedTextOptionType extends TextareaOptionType
{
    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        $values = ArrayUtil::trim(\explode("\n", StringUtil::unifyNewlines($value ?? '')));
        \uasort($values, 'strnatcmp');

        static $identifiers = [];
        do {
            $identifier = \substr(StringUtil::getRandomID(), 0, 8);
        } while (\in_array($identifier, $identifiers));
        $identifiers[] = $identifier;

        return WCF::getTPL()->fetch('shared_lineBreakSeparatedTextOptionType', 'wcf', [
            'identifier' => $identifier,
            'option' => $option,
            'values' => $values,
        ]);
    }
}
