<?php

namespace wcf\event\interaction\user;

use wcf\event\IPsr14Event;
use wcf\system\interaction\user\UserCardQuickInteractions;

/**
 * Indicates that the provider for user card quick interactions is collecting interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserCardQuickInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly UserCardQuickInteractions $provider) {}
}
