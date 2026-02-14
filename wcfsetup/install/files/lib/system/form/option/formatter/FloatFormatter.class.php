<?php

namespace wcf\system\form\option\formatter;

use wcf\util\StringUtil;

/**
 * Formatter for float values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class FloatFormatter implements IFormOptionFormatter
{
    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        $suffix = '';
        if (!empty($configuration['unit'])) {
            $suffix = ' ' . StringUtil::encodeHTML($configuration['unit']);
        }

        return StringUtil::formatNumeric(\floatval($value)) . $suffix;
    }
}
