<?php

namespace wcf\system\form\option;

use wcf\system\form\builder\field\ISelectionFormField;
use wcf\system\WCF;

/**
 * Provides helper methods for form options that use select options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
trait TSelectOptionsFormOption
{
    /**
     * @param array<string, mixed> $configuration
     */
    protected function setSelectOptions(ISelectionFormField $formField, array $configuration): void
    {
        if (!isset($configuration['selectOptions'])) {
            return;
        }

        $selectOptions = [];
        foreach (\json_decode($configuration['selectOptions'], true, flags: \JSON_THROW_ON_ERROR) as $selectOption) {
            if (isset($selectOption['value'][0])) {
                $value = $selectOption['value'][0];
            } else if (isset($selectOption['value'][WCF::getLanguage()->languageID])) {
                $value = $selectOption['value'][WCF::getLanguage()->languageID];
            } else {
                $value = reset($selectOption['value']);
            }

            $selectOptions[$selectOption['key']] = $value;
        }

        $formField->options($selectOptions);
    }
}
