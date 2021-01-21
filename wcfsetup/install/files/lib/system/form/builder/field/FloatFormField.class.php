<?php

namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for float values.
 *
 * If a non-required float field is left empty, its value is `0.0`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field
 * @since   5.2
 */
class FloatFormField extends AbstractNumericFormField
{
    /**
     * @inheritDoc
     * @since       5.4
     */
    protected function getValidAutoCompleteTokens(): array
    {
        return [
            'transaction-amount',
        ];
    }
}
