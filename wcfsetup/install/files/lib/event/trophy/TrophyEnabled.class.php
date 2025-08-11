<?php

namespace wcf\event\trophy;

use wcf\data\trophy\Trophy;
use wcf\event\IPsr14Event;

/**
 * Indicates that a trophy has been enabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class TrophyEnabled implements IPsr14Event
{
    public function __construct(public readonly Trophy $trophy) {}
}
