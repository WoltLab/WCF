<?php

namespace wcf\command\user\group\assignment;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentBuilder;
use wcf\event\user\group\assignment\UserGroupAssignmentCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new automatic user group assignment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateUserGroupAssignment
{
    public function __construct(
        private readonly UserGroupAssignmentBuilder $builder,
    ) {}

    public function __invoke(): UserGroupAssignment
    {
        $assignment = $this->builder->create();

        UserGroupAssignmentBuilder::resetCache();

        EventHandler::getInstance()->fire(new UserGroupAssignmentCreated($assignment, $this->builder));

        return $assignment;
    }
}
