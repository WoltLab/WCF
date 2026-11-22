<?php

namespace wcf\command\pageNotice;

use wcf\data\page\notice\PageNotice;
use wcf\data\page\notice\PageNoticeBuilder;
use wcf\event\pageNotice\PageNoticesDeleted;
use wcf\system\cache\eager\PageNoticeLocationCache;
use wcf\system\event\EventHandler;

/**
 * Deletes page notices including their embedded objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeletePageNotices
{
    /**
     * @param PageNotice[] $pageNotices
     */
    public function __construct(
        private readonly array $pageNotices,
    ) {}

    public function __invoke(): void
    {
        if ($this->pageNotices === []) {
            return;
        }

        PageNoticeBuilder::deleteAll(\array_values(\array_map(
            static fn(PageNotice $pageNotice) => $pageNotice->pageNoticeID,
            $this->pageNotices
        )));

        new PageNoticeLocationCache()->rebuild();

        EventHandler::getInstance()->fire(new PageNoticesDeleted($this->pageNotices));
    }
}
