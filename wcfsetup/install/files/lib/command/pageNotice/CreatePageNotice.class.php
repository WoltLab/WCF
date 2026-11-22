<?php

namespace wcf\command\pageNotice;

use wcf\data\page\notice\PageNotice;
use wcf\data\page\notice\PageNoticeBuilder;
use wcf\event\pageNotice\PageNoticeCreated;
use wcf\system\cache\eager\PageNoticeLocationCache;
use wcf\system\event\EventHandler;

/**
 * Creates a new page notice.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreatePageNotice
{
    public function __construct(
        private readonly PageNoticeBuilder $builder,
    ) {}

    public function __invoke(): PageNotice
    {
        $pageNotice = $this->builder->create();

        new PageNoticeLocationCache()->rebuild();

        EventHandler::getInstance()->fire(new PageNoticeCreated($pageNotice, $this->builder));

        return $pageNotice;
    }
}
