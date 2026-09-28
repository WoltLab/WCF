<?php

namespace wcf\event\ad;

use wcf\data\ad\Ad;
use wcf\event\IPsr14Event;

/**
 * Indicates that an ad has been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class AdDeleted implements IPsr14Event
{
    public function __construct(
        public readonly Ad $ad,
    ) {}
}
