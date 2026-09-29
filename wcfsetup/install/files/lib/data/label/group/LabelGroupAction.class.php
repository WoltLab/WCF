<?php

namespace wcf\data\label\group;

use wcf\command\label\group\DeleteLabelGroup;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes label group-related actions.
 *
 * Label groups should be created, updated and deleted through the
 * `CreateLabelGroup`, `UpdateLabelGroup` and `DeleteLabelGroup` commands, the
 * `create`, `update` and `delete` actions are `@deprecated 6.3`.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<LabelGroup, LabelGroupEditor>
 */
class LabelGroupAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = LabelGroupEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.content.label.canManageLabel'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.content.label.canManageLabel'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.content.label.canManageLabel'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'update'];

    /**
     * @deprecated 6.3 use the `DeleteLabelGroup` command instead.
     */
    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->objects as $object) {
            new DeleteLabelGroup($object->getDecoratedObject())();
        }

        return \count($this->objects);
    }
}
