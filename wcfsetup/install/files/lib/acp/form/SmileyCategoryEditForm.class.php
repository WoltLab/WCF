<?php

namespace wcf\acp\form;

/**
 * Shows the category edit form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Form
 */
class SmileyCategoryEditForm extends SmileyCategoryAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.smiley.category.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_SMILEY'];

    /**
     * @inheritDoc
     */
    public string $pageTitle = 'wcf.acp.smiley.category.edit';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';
}
