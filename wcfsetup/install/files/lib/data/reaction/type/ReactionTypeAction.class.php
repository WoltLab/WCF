<?php

namespace wcf\data\reaction\type;

use wcf\command\reaction\type\DeleteReactionType;
use wcf\command\reaction\type\DisableReactionType;
use wcf\command\reaction\type\EnableReactionType;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * ReactionType related actions.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @extends AbstractDatabaseObjectAction<ReactionType, ReactionTypeEditor>
 * @deprecated 6.3 use `ReactionTypeBuilder` with the `CreateReactionType` / `UpdateReactionType` commands instead.
 */
class ReactionTypeAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.content.reaction.canManageReactionType'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.content.reaction.canManageReactionType'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete', 'update'];

    /**
     * @deprecated 6.3 use the `DeleteReactionType` command instead.
     */
    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->objects as $object) {
            new DeleteReactionType($object->getDecoratedObject())();
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
     * @deprecated 6.3 use the `DisableReactionType` or `EnableReactionType` command instead.
     */
    #[\Override]
    public function toggle()
    {
        foreach ($this->getObjects() as $editor) {
            if ($editor->isAssignable !== 0) {
                new DisableReactionType($editor->getDecoratedObject())();
            } else {
                new EnableReactionType($editor->getDecoratedObject())();
            }
        }
    }
}
