<?php

namespace wcf\command\user\group\assignment;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentBuilder;
use wcf\event\user\group\assignment\UserGroupAssignmentDeleted;
use wcf\system\event\EventHandler;

/**
 * Deletes an automatic user group assignment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteUserGroupAssignment
{
    public function __construct(
        private readonly UserGroupAssignment $assignment,
    ) {}

    public function __invoke(): void
    {
        UserGroupAssignmentBuilder::delete($this->assignment);

        UserGroupAssignmentBuilder::resetCache();

        EventHandler::getInstance()->fire(new UserGroupAssignmentDeleted($this->assignment));
    }
}
