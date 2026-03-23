<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Option type implementation for textareas.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TextareaOptionType extends TextOptionType
{
    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, mixed $value)
    {
        return WCF::getTPL()->render('wcf', 'textareaOptionType', [
            'option' => $option,
            'value' => $value,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSearchFormElement(Option $option, mixed $value)
    {
        return WCF::getTPL()->render('wcf', 'textareaSearchableOptionType', [
            'option' => $option,
            'searchOption' => $this->forceSearchOption || ($value !== null && $value !== $option->defaultValue) || isset($_POST['searchOptions'][$option->optionName]),
            'value' => $value,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, mixed $newValue)
    {
        $newValue = StringUtil::unifyNewlines(parent::getData($option, $newValue));

        // check for wildcard
        if ($option->wildcard) {
            $values = \explode("\n", $newValue);
            if (\in_array($option->wildcard, $values)) {
                $newValue = $option->wildcard;
            }
        }

        return $newValue;
    }

    /**
     * @inheritDoc
     */
    public function compare(mixed $value1, mixed $value2)
    {
        $value1 = \explode("\n", StringUtil::unifyNewlines($value1));
        $value2 = \explode("\n", StringUtil::unifyNewlines($value2));

        // check if value1 contains more elements than value2
        $diff = \array_diff($value1, $value2);
        if (!empty($diff)) {
            return 1;
        }

        // check if value1 contains less elements than value2
        $diff = \array_diff($value2, $value1);
        if (!empty($diff)) {
            return -1;
        }

        // both lists are equal
        return 0;
    }
}
