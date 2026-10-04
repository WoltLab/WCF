<?php

namespace wcf\event\interaction\bulk\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\bulk\admin\UserBulkInteractions;

/**
 * Indicates that the provider for user bulk interactions is collecting interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserBulkInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly UserBulkInteractions $provider) {}
}
