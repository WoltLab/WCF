<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IAttributeFormField` methods for form fields relying on an `input` element.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @since       5.4
 */
trait TInputAttributeFormField
{
    use TAttributeFormField {
        getReservedFieldAttributes as private defaultGetReservedFieldAttributes;
    }

    /**
     * Returns a list of attributes that are not accessible via the field attribute methods.
     *
     * @return      string[]
     */
    protected static function getReservedFieldAttributes(): array
    {
        return \array_merge(
            static::defaultGetReservedFieldAttributes(),
            [
                'name',
                'type',
                'value',
            ]
        );
    }
}
