<?php

namespace wcf\data\user\rank;

use wcf\command\user\rank\DeleteUserRank;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user rank-related actions.
 *
 * User ranks should be created, updated and deleted through the
 * `CreateUserRank`, `UpdateUserRank` and `DeleteUserRank` commands, the
 * `create`, `update` and `delete` actions are `@deprecated 6.3`.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserRank, UserRankEditor>
 */
class UserRankAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.user.rank.canManageRank'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete'];

    /**
     * @deprecated 6.3 use the `DeleteUserRank` command instead.
     */
    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->objects as $object) {
            new DeleteUserRank($object->getDecoratedObject())();
        }

        return \count($this->objects);
    }
}
