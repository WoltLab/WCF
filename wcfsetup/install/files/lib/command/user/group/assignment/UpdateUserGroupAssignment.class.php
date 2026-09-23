<?php

namespace wcf\command\user\group\assignment;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentBuilder;
use wcf\event\user\group\assignment\UserGroupAssignmentUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates an automatic user group assignment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateUserGroupAssignment
{
    public function __construct(
        private readonly UserGroupAssignmentBuilder $builder,
    ) {}

    public function __invoke(): UserGroupAssignment
    {
        $assignment = $this->builder->update();

        UserGroupAssignmentBuilder::resetCache();

        EventHandler::getInstance()->fire(new UserGroupAssignmentUpdated($assignment, $this->builder));

        return $assignment;
    }
}
