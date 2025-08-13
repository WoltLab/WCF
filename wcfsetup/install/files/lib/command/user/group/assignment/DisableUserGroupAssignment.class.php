<?php

namespace wcf\command\user\group\assignment;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentEditor;
use wcf\event\user\group\assignment\UserGroupAssignmentDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables a user group assignment.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableUserGroupAssignment
{
    public function __construct(private readonly UserGroupAssignment $assignment) {}

    public function __invoke(): void
    {
        (new UserGroupAssignmentEditor($this->assignment))->update([
            'isDisabled' => 1,
        ]);

        $event = new UserGroupAssignmentDisabled($this->assignment);
        EventHandler::getInstance()->fire($event);
    }
}
