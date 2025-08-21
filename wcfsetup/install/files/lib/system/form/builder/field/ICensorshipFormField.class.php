<?php

namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports the use of the censorship function.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface ICensorshipFormField extends IFormField
{
    /**
     * Sets whether the censorship function should be applied to this field.
     */
    public function censorship(bool $censorship = true): static;

    /**
     * Returns `true` if the censorship function should be applied to this field.
     */
    public function hasCensorship(): bool;

    /**
     * Validates whether the text contains censored words.
     */
    public function validateCensorship(string $text): void;
}
