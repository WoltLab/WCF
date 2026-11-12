<?php

namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field to set the sort order.
 *
 * This field uses the `wcf.global.showOrder` language item as the default
 * form field label and uses `ASC` and `DESC` as the default options.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class SortOrderFormField extends AbstractSingleSelectFormField
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_selectFormField';

    public function __construct()
    {
        $this->label('wcf.global.showOrder');
        $this->options([
            'ASC' => 'wcf.global.sortOrder.ascending',
            'DESC' => 'wcf.global.sortOrder.descending',
        ]);
        $this->defaultValue('ASC');
    }

    #[\Override]
    protected static function getDefaultId(): string
    {
        return 'sortOrder';
    }
}
