<?php

namespace wcf\event\interaction\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\admin\UserInteractions;

/**
 * Indicates that the provider for user interactions is collecting interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly UserInteractions $provider) {}
}
