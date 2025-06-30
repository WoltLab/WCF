<?php

namespace wcf\event\interaction\admin;

use wcf\event\IPsr14Event;
use wcf\system\interaction\admin\UserGroupAssignmentInteractions;

/**
 * Indicates that the provider for user group assignment interactions is collecting interactions.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserGroupAssignmentInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly UserGroupAssignmentInteractions $provider) {}
}
