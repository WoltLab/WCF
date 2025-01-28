<?php

namespace wcf\data\user\option;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user option-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserOption      create()
 * @method  UserOptionEditor[]  getObjects()
 * @method  UserOptionEditor    getSingleObject()
 */
class UserOptionAction extends AbstractDatabaseObjectAction
{
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
    protected $permissionsUpdate = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'update'];
}
