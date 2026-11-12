<?php

namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for selecting a single value.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class SelectFormField extends AbstractSingleSelectFormField
{
    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_selectFormField';
}
