<?php

namespace wcf\system\form\builder\field;

/**
 * Every form field able to store its localized values in the `_l10n` table of
 * the database object has to implement this interface.
 *
 * The l10n mode is mutually exclusive with the i18n mode: While the i18n mode
 * stores the values as phrases in `wcf1_language_item`, the l10n mode exposes
 * the values via `getL10nValues()` for persistence through
 * `wcf\system\l10n\L10nStorage`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
interface IL10nFormField extends IFormField
{
    /**
     * Sets whether this field supports l10n input and returns this field.
     */
    public function l10n(bool $l10n = true): static;

    /**
     * Sets whether this field's value must be entered for every language and
     * returns this field. Enabling this also enables l10n support.
     */
    public function l10nRequired(bool $l10nRequired = true): static;

    /**
     * Returns `true` if this field supports l10n input.
     */
    public function isL10n(): bool;

    /**
     * Returns `true` if this field's value must be entered for every language.
     */
    public function isL10nRequired(): bool;

    /**
     * Returns the values of this field for persistence via `L10nStorage`:
     * `[L10nStorage::MONOLINGUAL => value]` for a monolingual value or
     * `[languageID => value, ...]` for multilingual values.
     *
     * @return array<int, string>
     */
    public function getL10nValues(): array;
}
