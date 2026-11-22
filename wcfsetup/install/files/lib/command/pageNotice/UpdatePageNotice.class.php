<?php

namespace wcf\command\pageNotice;

use wcf\data\page\notice\PageNotice;
use wcf\data\page\notice\PageNoticeBuilder;
use wcf\event\pageNotice\PageNoticeUpdated;
use wcf\system\cache\eager\PageNoticeLocationCache;
use wcf\system\event\EventHandler;

/**
 * Updates a page notice.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdatePageNotice
{
    public function __construct(
        private readonly PageNoticeBuilder $builder,
    ) {}

    public function __invoke(): PageNotice
    {
        $pageNotice = $this->builder->update();

        // The visibility of the notice may have changed.
        new PageNoticeLocationCache()->rebuild();

        EventHandler::getInstance()->fire(new PageNoticeUpdated($pageNotice, $this->builder));

        return $pageNotice;
    }
}
