<?php

namespace wcf\event\notice;

use wcf\data\notice\Notice;
use wcf\event\IPsr14Event;

/**
 * Indicates that a notice has been dismissed.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class NoticeDismissed implements IPsr14Event
{
    public function __construct(
        public readonly Notice $notice
    ) {}
}
