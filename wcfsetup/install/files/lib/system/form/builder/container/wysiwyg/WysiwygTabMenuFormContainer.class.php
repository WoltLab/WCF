<?php

namespace wcf\system\form\builder\container\wysiwyg;

use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\IFormChildNode;

/**
 * Represents a container whose children are tabs of a wysiwyg tab menu.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class WysiwygTabMenuFormContainer extends TabMenuFormContainer
{
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_wysiwygTabMenuFormContainer';

    /**
     * Creates a new instance of `WysiwygTabMenuFormContainer`.
     */
    public function __construct()
    {
        $this->removeClass('section')
            ->removeClass('tabMenuContainer')
            ->addClass('messageTabMenu');
    }

    /**
     * @inheritDoc
     */
    public function appendChild(IFormChildNode $child): static
    {
        $child->removeClass('tabMenuContent')
            ->addClass('messageTabMenuContent');

        return parent::appendChild($child);
    }
}
