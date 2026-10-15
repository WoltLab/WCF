<?php

namespace wcf\data\trophy;

use wcf\command\trophy\DeleteTrophy;
use wcf\command\trophy\DisableTrophy;
use wcf\command\trophy\EnableTrophy;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Trophy related actions.
 *
 * Trophies should be created, updated and deleted through the `CreateTrophy`,
 * `UpdateTrophy` and `DeleteTrophy` commands, the `create`, `update` and
 * `delete` actions are `@deprecated 6.3`.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Trophy, TrophyEditor>
 */
class TrophyAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.trophy.canManageTrophy'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.trophy.canManageTrophy'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['toggle', 'delete'];

    /**
     * @deprecated 6.3 use the `DeleteTrophy` command instead.
     */
    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->objects as $object) {
            new DeleteTrophy($object->getDecoratedObject())();
        }

        return \count($this->objects);
    }

    /**
     * @deprecated 6.3
     */
    #[\Override]
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnableTrophy` or `DisableTrophy` commands instead.
     */
    #[\Override]
    public function toggle()
    {
        foreach ($this->getObjects() as $editor) {
            if ($editor->isDisabled !== 0) {
                new EnableTrophy($editor->getDecoratedObject())();
            } else {
                new DisableTrophy($editor->getDecoratedObject())();
            }
        }
    }
}
