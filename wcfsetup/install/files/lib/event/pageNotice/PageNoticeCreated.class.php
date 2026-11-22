<?php

namespace wcf\event\pageNotice;

use wcf\data\page\notice\PageNotice;
use wcf\data\page\notice\PageNoticeBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a page notice has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PageNoticeCreated implements IPsr14Event
{
    public function __construct(
        public readonly PageNotice $pageNotice,
        public readonly PageNoticeBuilder $builder,
    ) {}
}
