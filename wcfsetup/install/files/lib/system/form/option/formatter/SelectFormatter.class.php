<?php

namespace wcf\system\form\option\formatter;


use wcf\util\StringUtil;

/**
 * Formatter for select values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SelectFormatter implements IFormOptionFormatter
{
    public function __construct(private readonly bool $encode = true) {}

    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        foreach (\json_decode($configuration['selectOptions'], true, flags: \JSON_THROW_ON_ERROR) as $selectOption) {
            if ($selectOption['key'] == $value) {
                if (isset($selectOption['value'][0])) {
                    $value = $selectOption['value'][0];
                } else if (isset($selectOption['value'][$languageID])) {
                    $value = $selectOption['value'][$languageID];
                } else {
                    $value = reset($selectOption['value']);
                }

                if ($this->encode) {
                    return StringUtil::encodeHTML($value);
                }

                return $value;
            }
        }

        return '';
    }
}
