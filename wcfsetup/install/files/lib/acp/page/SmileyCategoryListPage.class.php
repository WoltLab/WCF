<?php

namespace wcf\acp\page;

/**
 * Shows the smiley category list.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SmileyCategoryListPage extends AbstractCategoryListPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.smiley.category.list';

    /**
     * @inheritDoc
     */
    public $objectTypeName = 'com.woltlab.wcf.bbcode.smiley';

    /**
     * @inheritDoc
     */
    public $pageTitle = 'wcf.acp.smiley.category.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_SMILEY'];
}
