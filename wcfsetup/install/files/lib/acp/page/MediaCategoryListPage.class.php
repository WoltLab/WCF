<?php

namespace wcf\acp\page;

/**
 * Shows the list media categories.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class MediaCategoryListPage extends AbstractCategoryListPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.media.category.list';

    /**
     * @inheritDoc
     */
    public $objectTypeName = 'com.woltlab.wcf.media.category';
}
