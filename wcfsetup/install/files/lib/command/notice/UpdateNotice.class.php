<?php

namespace wcf\command\notice;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeBuilder;
use wcf\event\notice\NoticeUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates a notice.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateNotice
{
    public function __construct(
        private readonly NoticeBuilder $builder,
    ) {}

    public function __invoke(): Notice
    {
        $notice = $this->builder->update();

        NoticeBuilder::resetCache();

        EventHandler::getInstance()->fire(new NoticeUpdated($notice, $this->builder));

        return $notice;
    }
}
