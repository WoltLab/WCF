<?php

namespace wcf\data\reaction\type;

use wcf\command\file\DeleteFiles;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\file\FileList;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Builder for creating and updating reaction types.
 *
 * The localized title is stored in the `wcf1_reaction_type_l10n` table (see
 * `L10nStorage`). `l10nIdentifier` links a reaction type shipped with the
 * package to its language variable; it is `NULL` for reaction types created
 * by an administrator.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<ReactionType>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class ReactionTypeBuilder extends DatabaseObjectBuilder
{
    /**
     * @var L10nValue
     */
    private array $title;

    /**
     * @param L10nValue $title
     */
    public function setTitle(array $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setL10nIdentifier(?string $l10nIdentifier): static
    {
        $this->properties['l10nIdentifier'] = $l10nIdentifier;

        return $this;
    }

    public function setShowOrder(int $showOrder): static
    {
        $this->properties['showOrder'] = $showOrder;

        return $this;
    }

    public function setIconFileID(?int $iconFileID): static
    {
        $this->properties['iconFileID'] = $iconFileID;

        return $this;
    }

    public function setIsAssignable(bool $isAssignable): static
    {
        $this->properties['isAssignable'] = $isAssignable ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        // Shipped reaction types receive their title from the language
        // variable through `SyncL10nLanguageItems`.
        if (!isset($this->title) && ($this->properties['l10nIdentifier'] ?? null) === null) {
            throw new \BadMethodCallException("Missing value for 'title'.");
        }
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        if (isset($this->properties['showOrder'])) {
            $sql = "UPDATE  wcf1_reaction_type
                    SET     showOrder = showOrder + 1
                    WHERE   showOrder >= ?
                        AND reactionTypeID <> ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $object->showOrder,
                $object->reactionTypeID,
            ]);
        }

        if (isset($this->title)) {
            $this->saveL10nValues($object);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        $oldObject = $this->getObject();

        if (isset($this->properties['showOrder'])) {
            // Closes the gap at the previous position before making room at
            // the new one, the position is relative to the other reaction types.
            $sql = "UPDATE  wcf1_reaction_type
                    SET     showOrder = showOrder - 1
                    WHERE   showOrder > ?
                        AND reactionTypeID <> ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $oldObject->showOrder,
                $object->reactionTypeID,
            ]);

            $sql = "UPDATE  wcf1_reaction_type
                    SET     showOrder = showOrder + 1
                    WHERE   showOrder >= ?
                        AND reactionTypeID <> ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $object->showOrder,
                $object->reactionTypeID,
            ]);
        }

        if (isset($this->title)) {
            $this->saveL10nValues($object);
        }

        // A reaction type owns its icon file, a replaced file is orphaned.
        if (
            \array_key_exists('iconFileID', $this->properties)
            && $oldObject->iconFileID !== null
            && $oldObject->iconFileID !== $object->iconFileID
        ) {
            $fileList = new FileList();
            $fileList->setObjectIDs([$oldObject->iconFileID]);
            $fileList->readObjects();
            $files = \array_values($fileList->getObjects());

            if ($files !== []) {
                new DeleteFiles($files)();
            }
        }
    }

    private function saveL10nValues(ReactionType $reactionType): void
    {
        (new L10nStorage(ReactionType::getL10nDefinition()))->setValues(
            $reactionType->reactionTypeID,
            [
                'title' => $this->title,
            ]
        );
    }
}
