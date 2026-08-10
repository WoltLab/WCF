<?php

namespace wcf\system\option;

use wcf\data\option\Option;

/**
 * Option type implementation for url input fields.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class URLOptionType extends TextOptionType
{
    #[\Override]
    protected function getContent(Option $option, mixed $newValue)
    {
        if (!empty($newValue) && !\preg_match('~^https?://~i', $newValue)) {
            $newValue = 'https://' . $newValue;
        }

        return parent::getContent($option, $newValue);
    }
}
