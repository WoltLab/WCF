<?php

namespace wcf\system\form\builder\container;

/**
 * Represents a form container whose children are displayed in rows.
 *
 * While objects of this class support setting (and getting) labels and descriptions, they are not
 * shown in the actual form!
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class RowFormContainer extends FormContainer
{
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_rowFormContainer';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->addClasses(['row', 'rowColGap', 'formGrid']);
    }
}
