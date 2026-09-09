<?php

namespace wcf\event\gridView\admin;

use wcf\event\IPsr14Event;
use wcf\system\gridView\admin\SitemapGridView;

/**
 * Indicates that the sitemap grid view has been initialized.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SitemapGridViewInitialized implements IPsr14Event
{
    public function __construct(
        public readonly SitemapGridView $gridView
    ) {}
}
