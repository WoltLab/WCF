<?php

namespace wcf\system\form\option\formatter;

use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Formatter for multiple selection values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class MultipleSelectionFormatter implements IFormOptionFormatter
{
    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        if (!$value) {
            return '';
        };

        $keys = \explode(",", $value);
        $selectOptions = JSON::decode($configuration['selectOptions']);
        $html = '';

        foreach ($keys as $key) {
            foreach ($selectOptions as $selectOption) {
                if ($selectOption['key'] == $key) {
                    if (isset($selectOption['value'][0])) {
                        $value = $selectOption['value'][0];
                    } else if (isset($selectOption['value'][$languageID])) {
                        $value = $selectOption['value'][$languageID];
                    } else {
                        $value = reset($selectOption['value']);
                    }

                    if ($html !== '') {
                        $html .= ', ';
                    }

                    $html .= StringUtil::encodeHTML($value);
                }
            }
        }

        return $html;
    }
}
