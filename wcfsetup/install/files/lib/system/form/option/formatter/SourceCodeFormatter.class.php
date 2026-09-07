<?php

namespace wcf\system\form\option\formatter;

use wcf\util\StringUtil;

/**
 * Formatter for source code values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SourceCodeFormatter implements IFormOptionFormatter
{
    #[\Override]
    public function format(string $value, int $languageID, array $configuration): string
    {
        return '<pre style="overflow: auto">' . StringUtil::encodeHTML($value) . '</pre>';
    }
}
