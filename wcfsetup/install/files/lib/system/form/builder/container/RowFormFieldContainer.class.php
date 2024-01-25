<?php

namespace wcf\system\form\builder\container;

use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\IFormChildNode;

/**
 * Represents a form container whose children which are displayed in rows and which may only be form
 * fields.
 *
 * In contrast to `RowFormContainer`, the labels and descriptions of the form fields in this container
 * are not shown, instead there the container's label and description applies to all form fields.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class RowFormFieldContainer extends FormContainer
{
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_rowFormFieldContainer';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public function appendChild(IFormChildNode $child): static
    {
        if ((!$child instanceof IFormField)) {
            throw new \InvalidArgumentException(
                "'" . static::class . "' only supports '" . IFormField::class . "' instances as children for container '{$this->getId()}'."
            );
        }

        return parent::appendChild($child);
    }
}
