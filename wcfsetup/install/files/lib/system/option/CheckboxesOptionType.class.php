<?php

namespace wcf\system\option;

/**
 * Option type implementation for checkboxes.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CheckboxesOptionType extends MultiSelectOptionType
{
    /**
     * @inheritDoc
     */
    protected $formElementTemplate = 'shared_checkboxesOptionType';

    /**
     * @inheritDoc
     */
    protected $searchableFormElementTemplate = 'shared_checkboxesSearchableOptionType';

    /**
     * @inheritDoc
     */
    public function getCSSClassName()
    {
        return 'checkboxList';
    }
}
