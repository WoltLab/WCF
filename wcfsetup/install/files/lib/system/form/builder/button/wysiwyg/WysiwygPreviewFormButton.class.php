<?php

namespace wcf\system\form\builder\button\wysiwyg;

use wcf\system\form\builder\button\FormButton;
use wcf\system\form\builder\IObjectTypeFormNode;
use wcf\system\form\builder\TObjectTypeFormNode;
use wcf\system\form\builder\TWysiwygFormNode;

/**
 * Represents a preview button for a wysiwyg field.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class WysiwygPreviewFormButton extends FormButton implements IObjectTypeFormNode
{
    use TObjectTypeFormNode;
    use TWysiwygFormNode;

    /**
     * id of the previewed message
     * @var int
     */
    protected $objectId = 0;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_wysiwygPreviewFormButton';

    public function __construct()
    {
        $this->label('wcf.global.button.preview');
    }

    /**
     * Returns the id of the previewed message.
     *
     * By default, `0` is returned.
     *
     * @return  int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    #[\Override]
    public function getObjectTypeDefinition()
    {
        return 'com.woltlab.wcf.message';
    }

    /**
     * Sets the id of the previewed message and returns this button.
     *
     * @return  WysiwygPreviewFormButton    this button
     */
    public function objectId(int $objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }
}
