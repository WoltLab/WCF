<?php

namespace wcf\acp\form;

/**
 * Shows the media category add form.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class MediaCategoryAddForm extends CategoryAddFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.media.category.add';

    /**
     * @inheritDoc
     */
    public string $objectTypeName = 'com.woltlab.wcf.media.category';

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = MediaCategoryEditForm::class;
}
