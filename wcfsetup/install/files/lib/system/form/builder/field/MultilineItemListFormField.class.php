<?php

namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field that allows entering a list of items.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 */
class MultilineItemListFormField extends ItemListFormField implements INullableFormField
{
    use TNullableFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = '__multilineItemListFormField';

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/MultilineItemList';

    /**
     * @see TFilterableSelectionFormField::$filterable
     */
    protected bool $filterable = false;

    /**
     * @see TFilterableSelectionFormField::filterable()
     */
    public function filterable(bool $filterable = true): self
    {
        $this->filterable = $filterable;

        return $this;
    }

    /**
     * @see TFilterableSelectionFormField::isFilterable()
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }
}
