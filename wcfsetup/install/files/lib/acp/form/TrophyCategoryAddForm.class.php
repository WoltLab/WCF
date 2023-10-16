<?php

namespace wcf\acp\form;

/**
 * Represents the trophy category add form.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class TrophyCategoryAddForm extends CategoryAddFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.trophy.category.add';

    /**
     * @inheritDoc
     */
    public string $objectTypeName = 'com.woltlab.wcf.trophy.category';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TROPHY'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.trophy.canManageTrophy'];

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = TrophyCategoryEditForm::class;
}
