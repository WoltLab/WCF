<?php

namespace wcf\event\interaction\user;

use wcf\event\IPsr14Event;
use wcf\system\interaction\user\UserManagementInteractions;

/**
 * Indicates that the provider for user management interactions is collecting interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserManagementInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly UserManagementInteractions $provider) {}
}
