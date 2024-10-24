<?php

namespace wcf\data\reaction\type;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
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
 * @method  ReactionTypeEditor[]        getObjects()
 * @method  ReactionTypeEditor      getSingleObject()
 */
class ReactionTypeAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction
{
    use TDatabaseObjectToggle;

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
    protected $requireACP = ['delete', 'update', 'updatePosition'];

    /**
     * @inheritDoc
     */
    public function create()
    {
        if (isset($this->parameters['data']['showOrder']) && $this->parameters['data']['showOrder'] !== null) {
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

                \rename($iconFile->getLocation(), WCF_DIR . '/images/reaction/' . $fileName);
                $iconFile->setProcessed(WCF_DIR . '/images/reaction/' . $fileName);

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

                    \rename($iconFile->getLocation(), WCF_DIR . '/images/reaction/' . $fileName);
                    $iconFile->setProcessed(WCF_DIR . '/images/reaction/' . $fileName);

                    $updateData['iconFile'] = $fileName;
                }
            }

            // update show order
            if (isset($this->parameters['data']['showOrder']) && $this->parameters['data']['showOrder'] !== null) {
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
    public function validateUpdatePosition()
    {
        // validate permissions
        if (\is_array($this->permissionsUpdate) && \count($this->permissionsUpdate)) {
            WCF::getSession()->checkPermissions($this->permissionsUpdate);
        } else {
            throw new PermissionDeniedException();
        }

        if (!isset($this->parameters['data']['structure'])) {
            throw new UserInputException('structure');
        }

        $this->readInteger('offset', true, 'data');
    }

    /**
     * @inheritDoc
     */
    public function updatePosition()
    {
        $reactionTypeList = new ReactionTypeList();
        $reactionTypeList->readObjects();

        $i = $this->parameters['data']['offset'];
        WCF::getDB()->beginTransaction();
        foreach ($this->parameters['data']['structure'][0] as $reactionTypeID) {
            $reactionType = $reactionTypeList->search($reactionTypeID);
            if ($reactionType === null) {
                continue;
            }

            $editor = new ReactionTypeEditor($reactionType);
            $editor->update(['showOrder' => $i++]);
        }
        WCF::getDB()->commitTransaction();
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
            if (isset($object->iconFile) && \file_exists(WCF_DIR . '/images/reaction/' . $object->iconFile)) {
                @\unlink(WCF_DIR . '/images/reaction/' . $object->iconFile);
            }
        }

        return $returnValues;
    }

    /**
     * @inheritDoc
     */
    public function toggle()
    {
        foreach ($this->getObjects() as $object) {
            $object->update([
                'isAssignable' => $object->isAssignable ? 0 : 1,
            ]);
        }
    }
}
