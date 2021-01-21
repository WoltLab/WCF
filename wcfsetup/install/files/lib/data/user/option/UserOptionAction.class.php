<?php

namespace wcf\data\user\option;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes user option-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Option
 *
 * @method  UserOption      create()
 * @method  UserOptionEditor[]  getObjects()
 * @method  UserOptionEditor    getSingleObject()
 */
class UserOptionAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TDatabaseObjectToggle;

    /**
     * @inheritDoc
     */
    protected $className = UserOptionEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'toggle', 'update'];

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        parent::validateDelete();

        foreach ($this->getObjects() as $userOption) {
            if (!$userOption->canDelete()) {
                throw new PermissionDeniedException();
            }
        }
    }
}
