<?php

namespace wcf\event\trophy;

use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a trophy has been updated.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TrophyUpdated implements IPsr14Event
{
    public function __construct(
        public readonly Trophy $trophy,
        public readonly TrophyBuilder $builder,
    ) {}
}
