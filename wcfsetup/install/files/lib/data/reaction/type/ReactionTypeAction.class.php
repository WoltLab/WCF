<?php

namespace wcf\data\reaction\type;

use wcf\command\file\DeleteFiles;
use wcf\command\reaction\type\DisableReactionType;
use wcf\command\reaction\type\EnableReactionType;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\file\FileList;
use wcf\data\IToggleAction;
use wcf\system\WCF;

/**
 * ReactionType related actions.
 *
 * Reaction types should be created and updated through the
 * `CreateReactionType` and `UpdateReactionType` commands, the `create` and
 * `update` actions are `@deprecated 6.3`.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @extends AbstractDatabaseObjectAction<ReactionType, ReactionTypeEditor>
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

    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        $fileIDs = [];
        foreach ($this->objects as $object) {
            if ($object->iconFileID !== null) {
                $fileIDs[] = $object->iconFileID;
            }
        }

        $returnValues = parent::delete();

        $sql = "UPDATE  wcf1_reaction_type
                SET     showOrder = showOrder - 1
                WHERE   showOrder > ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($this->getObjects() as $object) {
            $statement->execute([
                $object->showOrder,
            ]);
        }

        $this->deleteFiles($fileIDs);

        return $returnValues;
    }

    /**
     * @param list<int> $fileIDs
     */
    private function deleteFiles(array $fileIDs): void
    {
        if ($fileIDs === []) {
            return;
        }

        $fileList = new FileList();
        $fileList->setObjectIDs($fileIDs);
        $fileList->readObjects();
        $files = \array_values($fileList->getObjects());

        if ($files !== []) {
            new DeleteFiles($files)();
        }
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
