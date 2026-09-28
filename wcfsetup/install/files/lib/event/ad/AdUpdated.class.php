<?php

namespace wcf\event\ad;

use wcf\data\ad\Ad;
use wcf\data\ad\AdBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that an ad has been updated.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class AdUpdated implements IPsr14Event
{
    public function __construct(
        public readonly Ad $ad,
        public readonly AdBuilder $builder,
    ) {}
}
