<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\SitemapGridView;

/**
 * Shows a list of sitemap objects.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<SitemapGridView>
 */
class SitemapListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.maintenance.sitemap';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canRebuildData'];

    #[\Override]
    protected function createGridView(): SitemapGridView
    {
        return new SitemapGridView();
    }
}
