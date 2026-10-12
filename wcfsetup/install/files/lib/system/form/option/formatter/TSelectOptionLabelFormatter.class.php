<?php

namespace wcf\system\form\option\formatter;

use wcf\system\language\LanguageFactory;

/**
 * Resolves the labels of select options that reference a language item.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TSelectOptionLabelFormatter
{
    /**
     * Returns the label of the select option, resolving it if it is the name
     * of a language item, see `TSelectionFormField::options()`.
     */
    private function resolveLabel(string $label, int $languageID): string
    {
        if (\preg_match('~^([a-zA-Z0-9-_]+\.){2,}[a-zA-Z0-9-_]+$~', $label) !== 1) {
            return $label;
        }

        $language = LanguageFactory::getInstance()->getLanguage($languageID)
            ?? LanguageFactory::getInstance()->getDefaultLanguage();

        return $language->getDynamicVariable($label);
    }
}
