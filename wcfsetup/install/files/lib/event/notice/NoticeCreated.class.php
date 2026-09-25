<?php

namespace wcf\event\notice;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a notice has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class NoticeCreated implements IPsr14Event
{
    public function __construct(
        public readonly Notice $notice,
        public readonly NoticeBuilder $builder,
    ) {}
}
