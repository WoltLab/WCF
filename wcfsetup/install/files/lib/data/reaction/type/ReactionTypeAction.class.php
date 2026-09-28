<?php

namespace wcf\data\reaction\type;

use wcf\command\file\DeleteFiles;
use wcf\command\reaction\type\DisableReactionType;
use wcf\command\reaction\type\EnableReactionType;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\file\FileList;
use wcf\data\IToggleAction;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * ReactionType related actions.
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
    public function create()
    {
        if (isset($this->parameters['data']['showOrder'])) {
            $sql = "UPDATE  wcf1_reaction_type
                    SET     showOrder = showOrder + 1
                    WHERE   showOrder >= ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $this->parameters['data']['showOrder'],
            ]);
        }

        // The title cannot be empty by design, but cannot be filled proper if the
        // multilingualism is enabled, therefore, we must fill the tilte with a dummy value.
        if (!isset($this->parameters['data']['title']) && isset($this->parameters['title_i18n'])) {
            $this->parameters['data']['title'] = 'wcf.reactionType.title';
        }

        /** @var ReactionType $reactionType */
        $reactionType = parent::create();
        $reactionTypeEditor = new ReactionTypeEditor($reactionType);

        // i18n
        if (isset($this->parameters['title_i18n'])) {
            I18nHandler::getInstance()->save(
                $this->parameters['title_i18n'],
                'wcf.reactionType.title' . $reactionType->reactionTypeID,
                'wcf.reactionType',
                1
            );

            $reactionTypeEditor->update([
                'title' => 'wcf.reactionType.title' . $reactionType->reactionTypeID,
            ]);
        }

        return $reactionType;
    }

    #[\Override]
    public function update()
    {
        $replacedFileIDs = [];
        if (\array_key_exists('iconFileID', $this->parameters['data'] ?? [])) {
            if ($this->objects === []) {
                $this->readObjects();
            }

            foreach ($this->objects as $object) {
                $fileID = $object->iconFileID;
                if ($fileID !== null && $fileID !== $this->parameters['data']['iconFileID']) {
                    $replacedFileIDs[] = $fileID;
                }
            }
        }

        parent::update();

        foreach ($this->getObjects() as $object) {
            $updateData = [];

            // i18n
            if (isset($this->parameters['title_i18n'])) {
                I18nHandler::getInstance()->save(
                    $this->parameters['title_i18n'],
                    'wcf.reactionType.title' . $object->reactionTypeID,
                    'wcf.reactionType',
                    1
                );

                $updateData['title'] = 'wcf.reactionType.title' . $object->reactionTypeID;
            }

            // update show order
            if (isset($this->parameters['data']['showOrder'])) {
                $sql = "UPDATE  wcf1_reaction_type
                        SET     showOrder = showOrder + 1
                        WHERE   showOrder >= ?
                        AND     reactionTypeID <> ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $this->parameters['data']['showOrder'],
                    $object->reactionTypeID,
                ]);

                $sql = "UPDATE  wcf1_reaction_type
                        SET     showOrder = showOrder - 1
                        WHERE   showOrder > ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $object->showOrder,
                ]);
            }

            if ($updateData !== []) {
                $object->update($updateData);
            }
        }

        $this->deleteFiles($replacedFileIDs);
    }

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
