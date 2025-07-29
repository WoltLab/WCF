<?php

namespace wcf\data\user\group;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\user\group\command\CopyUserGroup;
use wcf\system\WCF;

/**
 * Executes user group-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserGroup, UserGroupEditor>
 */
class UserGroupAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    public $className = UserGroupEditor::class;

    /**
     * editor object for the copied user group
     * @var UserGroupEditor
     */
    public $groupEditor;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.user.canAddGroup'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.user.canDeleteGroup'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.user.canEditGroup'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['copy', 'create', 'delete', 'update'];

    /**
     * @inheritDoc
     * @return  UserGroup
     */
    public function create()
    {
        /** @var UserGroup $group */
        $group = parent::create();

        if (isset($this->parameters['options'])) {
            $groupEditor = new UserGroupEditor($group);
            $groupEditor->updateGroupOptions($this->parameters['options']);
        }

        return $group;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $object) {
            $object->update($this->parameters['data']);
            $object->updateGroupOptions($this->parameters['options']);
        }
    }

    /**
     * Validates the 'copy' action.
     * @deprecated 6.2 Use `CopyUserGroup` instead.
     */
    public function validateCopy()
    {
        WCF::getSession()->checkPermissions([
            'admin.user.canAddGroup',
            'admin.user.canEditGroup',
        ]);

        $this->readBoolean('copyACLOptions');
        $this->readBoolean('copyMembers');
        $this->readBoolean('copyUserGroupOptions');

        $this->groupEditor = $this->getSingleObject();
        if (!$this->groupEditor->canCopy()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Copies a user group.
     * @deprecated 6.2 Use `CopyUserGroup` instead.
     */
    public function copy()
    {
        $command = new CopyUserGroup(
            $this->groupEditor->getDecoratedObject(),
            $this->parameters['copyUserGroupOptions'],
            $this->parameters['copyMembers'],
            $this->parameters['copyACLOptions']
        );
        $group = $command();

        return [
            'groupID' => $group->groupID,
            'redirectURL' => LinkHandler::getInstance()->getLink('UserGroupEdit', [
                'id' => $group->groupID,
            ]),
        ];
    }
}
