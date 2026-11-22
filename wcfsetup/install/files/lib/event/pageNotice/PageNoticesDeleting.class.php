<?php

namespace wcf\event\pageNotice;

use wcf\data\page\notice\PageNotice;
use wcf\event\IPsr14Event;

/**
 * Indicates that page notices are about to be deleted.
 * Fired before any data is removed, allowing the cleanup of linked data.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PageNoticesDeleting implements IPsr14Event
{
    /**
     * @param PageNotice[] $pageNotices
     */
    public function __construct(
        public readonly array $pageNotices,
    ) {}
}
