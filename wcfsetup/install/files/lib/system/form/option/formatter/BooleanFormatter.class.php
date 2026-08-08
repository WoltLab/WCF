<?php

namespace wcf\system\form\option\formatter;

/**
 * Formatter for boolean values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class BooleanFormatter implements IFormOptionFormatter
{
    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        if ($value === '' || $value === '0') {
            return '';
        }

        return '✔️';
    }
}
