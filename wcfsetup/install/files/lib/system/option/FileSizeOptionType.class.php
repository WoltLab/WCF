<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Option type implementation for file sizes.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class FileSizeOptionType extends TextOptionType
{
    /**
     * @inheritDoc
     */
    protected $inputClass = 'short textRight';

    /**
     * @inheritDoc
     */
    public function getContent(Option $option, $newValue)
    {
        $number = \str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $newValue);
        $number = \str_replace(WCF::getLanguage()->get('wcf.global.decimalPoint'), '.', $number);

        if (!\preg_match('~^(?:\d*)\.?\d+~', $number, $matches)) {
            return 0;
        }

        $number = $matches[0];
        if (\preg_match('/[kmgt]i?b$/i', $newValue, $multiplier)) {
            switch (\mb_strtolower($multiplier[0])) {
                case 'tb':
                    $number *= 1000;
                    // no break
                case 'gb':
                    $number *= 1000;
                    // no break
                case 'mb':
                    $number *= 1000;
                    // no break
                case 'kb':
                    $number *= 1000;
                    break;
                case 'tib':
                    $number *= 1024;
                    // no break
                case 'gib':
                    $number *= 1024;
                    // no break
                case 'mib':
                    $number *= 1024;
                    // no break
                case 'kib':
                    $number *= 1024;
                    break;
            }
        }

        return $number;
    }

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        $value = FileUtil::formatFilesize(\intval($value));

        return parent::getFormElement($option, $value);
    }

    /**
     * @inheritDoc
     */
    public function compare($value1, $value2)
    {
        if ($value1 == $value2) {
            return 0;
        }

        return ($value1 > $value2) ? 1 : -1;
    }
}
