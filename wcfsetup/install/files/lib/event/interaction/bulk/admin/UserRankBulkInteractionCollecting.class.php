<?php

namespace wcf\event\interaction\bulk\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\bulk\admin\UserRankBulkInteractions;

/**
 * Indicates that the provider for user rank bulk interactions is collecting interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserRankBulkInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly UserRankBulkInteractions $provider) {}
}
