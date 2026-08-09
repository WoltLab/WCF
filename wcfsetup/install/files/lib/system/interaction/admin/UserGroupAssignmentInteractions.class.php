<?php

namespace wcf\system\interaction\admin;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\event\interaction\admin\UserGroupAssignmentInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for user group assignments.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserGroupAssignmentInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.user.canManageGroupAssignment')) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction("core/users/groups/assignments/%s")
        ]);

        EventHandler::getInstance()->fire(
            new UserGroupAssignmentInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return UserGroupAssignment::class;
    }
}
