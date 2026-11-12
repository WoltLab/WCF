<?php

namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for selecting a single value out of a larger
 * list of options that can be filtered by the user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class FilterableSelectFormField extends AbstractSingleSelectFormField
{
    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/RadioButton';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_filterableSelectFormField';

    #[\Override]
    public function getLabelTarget(): ?string
    {
        return null;
    }
}
