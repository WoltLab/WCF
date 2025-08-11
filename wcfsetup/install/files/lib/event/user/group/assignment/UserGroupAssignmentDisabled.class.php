<?php

namespace wcf\event\user\group\assignment;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user group assignment has been disabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserGroupAssignmentDisabled implements IPsr14Event
{
    public function __construct(public readonly UserGroupAssignment $assignment) {}
}
