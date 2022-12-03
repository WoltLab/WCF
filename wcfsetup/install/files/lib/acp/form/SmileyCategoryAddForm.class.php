<?php

namespace wcf\acp\form;

/**
 * Shows the smiley category add form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Category
 */
class SmileyCategoryAddForm extends AbstractCategoryAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.smiley.category.add';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_SMILEY'];

    /**
     * @inheritDoc
     */
    public string $pageTitle = 'wcf.acp.smiley.category.add';

    /**
     * @inheritDoc
     */
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.bbcode.smiley';
    }
}
