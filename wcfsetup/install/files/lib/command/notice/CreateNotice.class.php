<?php

namespace wcf\command\notice;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeBuilder;
use wcf\event\notice\NoticeCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new notice.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateNotice
{
    public function __construct(
        private readonly NoticeBuilder $builder,
    ) {}

    public function __invoke(): Notice
    {
        $notice = $this->builder->create();

        NoticeBuilder::resetCache();

        EventHandler::getInstance()->fire(new NoticeCreated($notice, $this->builder));

        return $notice;
    }
}
