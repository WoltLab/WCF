<?php

namespace wcf\system\form\option\formatter;

/**
 * Represents a formatter for the values of form options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IFormOptionFormatter
{
    /**
     * @param array<string, mixed> $configuration
     */
    public function format(string $value, int $languageID, array $configuration): string;
}
