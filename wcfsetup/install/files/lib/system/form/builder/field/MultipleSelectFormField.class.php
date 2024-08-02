<?php

namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for selecting multiple values in a select input field.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
class MultipleSelectFormField extends MultipleSelectionFormField
{
    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Select';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_multipleSelectFormField';
}
