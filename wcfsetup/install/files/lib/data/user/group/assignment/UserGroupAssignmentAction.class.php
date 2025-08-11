<?php

namespace wcf\data\user\group\assignment;

use wcf\command\user\group\assignment\DisableUserGroupAssignment;
use wcf\command\user\group\assignment\EnableUserGroupAssignment;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\condition\ConditionHandler;

/**
 * Executes user group assignment-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserGroupAssignment, UserGroupAssignmentEditor>
 */
class UserGroupAssignmentAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.user.canManageGroupAssignment'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.user.canManageGroupAssignment'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'toggle', 'update'];

    /**
     * @inheritDoc
     */
    public function delete()
    {
        ConditionHandler::getInstance()->deleteConditions(
            'com.woltlab.wcf.condition.userGroupAssignment',
            $this->objectIDs
        );

        return parent::delete();
    }

    /**
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnableUserGroupAssignment` or `DisableUserGroupAssignment` commands instead.
     */
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled) {
                (new EnableUserGroupAssignment($editor->getDecoratedObject()))();
            } else {
                (new DisableUserGroupAssignment($editor->getDecoratedObject()))();
            }
        }
    }
}
