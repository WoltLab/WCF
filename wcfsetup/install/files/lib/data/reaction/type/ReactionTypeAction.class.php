<?php

namespace wcf\data\reaction\type;

use wcf\command\reaction\type\DisableReactionType;
use wcf\command\reaction\type\EnableReactionType;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\file\upload\UploadFile;
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

    /**
     * @inheritDoc
     */
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
        $updateData = [];
        if (isset($this->parameters['title_i18n'])) {
            I18nHandler::getInstance()->save(
                $this->parameters['title_i18n'],
                'wcf.reactionType.title' . $reactionType->reactionTypeID,
                'wcf.reactionType',
                1
            );

            $updateData['title'] = 'wcf.reactionType.title' . $reactionType->reactionTypeID;
        }

        // image
        if (isset($this->parameters['iconFile']) && \is_array($this->parameters['iconFile'])) {
            $iconFile = \reset($this->parameters['iconFile']);
            if (!($iconFile instanceof UploadFile)) {
                throw new \InvalidArgumentException("The parameter 'image' is no instance of '" . UploadFile::class . "', instance of '" . \get_class($iconFile) . "' given.");
            }

            // save new image
            if (!$iconFile->isProcessed()) {
                $fileName = $reactionType->reactionTypeID . '-' . $iconFile->getFilename();

                \rename($iconFile->getLocation(), WCF_DIR . 'images/reaction/' . $fileName);
                $iconFile->setProcessed(WCF_DIR . 'images/reaction/' . $fileName);

                $updateData['iconFile'] = $fileName;
            }
        }

        if (!empty($updateData)) {
            $reactionTypeEditor->update($updateData);
        }

        return $reactionType;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
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

            // delete orphaned images
            if (isset($this->parameters['iconFile_removedFiles']) && \is_array($this->parameters['iconFile_removedFiles'])) {
                /** @var UploadFile $file */
                foreach ($this->parameters['iconFile_removedFiles'] as $file) {
                    @\unlink($file->getLocation());
                }
            }

            // image
            if (isset($this->parameters['iconFile']) && \is_array($this->parameters['iconFile'])) {
                $iconFile = \reset($this->parameters['iconFile']);
                if (!($iconFile instanceof UploadFile)) {
                    throw new \InvalidArgumentException("The parameter 'image' is no instance of '" . UploadFile::class . "', instance of '" . \get_class($iconFile) . "' given.");
                }

                // save new image
                if (!$iconFile->isProcessed()) {
                    $fileName = $object->reactionTypeID . '-' . $iconFile->getFilename();

                    \rename($iconFile->getLocation(), WCF_DIR . 'images/reaction/' . $fileName);
                    $iconFile->setProcessed(WCF_DIR . 'images/reaction/' . $fileName);

                    $updateData['iconFile'] = $fileName;
                }
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

            if (!empty($updateData)) {
                $object->update($updateData);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $returnValues = parent::delete();

        $sql = "UPDATE  wcf1_reaction_type
                SET     showOrder = showOrder - 1
                WHERE   showOrder > ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($this->getObjects() as $object) {
            $statement->execute([
                $object->showOrder,
            ]);

            // Delete outdated reaction type icon.
            if (isset($object->iconFile) && \file_exists(WCF_DIR . 'images/reaction/' . $object->iconFile)) {
                @\unlink(WCF_DIR . 'images/reaction/' . $object->iconFile);
            }
        }

        return $returnValues;
    }

    /**
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @inheritDoc
     *
     * @deprecated 6.3 use the `DisableReactionType` or `EnableReactionType` command instead.
     */
    public function toggle()
    {
        foreach ($this->getObjects() as $editor) {
            if ($editor->isAssignable) {
                (new DisableReactionType($editor->getDecoratedObject()))();
            } else {
                (new EnableReactionType($editor->getDecoratedObject()))();
            }
        }
    }
}
