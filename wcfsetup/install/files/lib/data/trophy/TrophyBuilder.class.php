<?php

namespace wcf\data\trophy;

use wcf\command\file\DeleteFiles;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting trophies.
 *
 * The localized title and description are stored in the `wcf1_trophy_l10n`
 * table (see `L10nStorage`).
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Trophy>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class TrophyBuilder extends DatabaseObjectBuilder
{
    /**
     * @var L10nValue
     */
    private array $title;

    /**
     * @var L10nValue
     */
    private array $description;

    private ?int $showOrder = null;

    /**
     * @param L10nValue $title
     */
    public function setTitle(array $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param L10nValue $description
     */
    public function setDescription(array $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setCategoryID(int $categoryID): static
    {
        $this->properties['categoryID'] = $categoryID;

        return $this;
    }

    /**
     * Sets the type of the trophy (`Trophy::TYPE_IMAGE` or `Trophy::TYPE_BADGE`)
     * and resets the properties that are exclusive to the other type.
     */
    public function setType(int $type): static
    {
        $this->properties['type'] = $type;

        if ($type === Trophy::TYPE_IMAGE) {
            $this->properties['iconName'] = '';
            $this->properties['iconColor'] = '';
            $this->properties['badgeColor'] = '';
            $this->properties['borderColor'] = '';
        } elseif ($type === Trophy::TYPE_BADGE) {
            $this->properties['iconFileID'] = null;
        }

        return $this;
    }

    /**
     * Sets the file of the image of an image trophy.
     */
    public function setIconFileID(?int $iconFileID): static
    {
        $this->properties['iconFileID'] = $iconFileID;

        return $this;
    }

    /**
     * Sets the icon and the colors of a badge trophy.
     */
    public function setBadge(string $iconName, string $iconColor, string $badgeColor, string $borderColor): static
    {
        $this->properties['iconName'] = $iconName;
        $this->properties['iconColor'] = $iconColor;
        $this->properties['badgeColor'] = $badgeColor;
        $this->properties['borderColor'] = $borderColor;

        return $this;
    }

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = (int)$isDisabled;

        return $this;
    }

    public function setAwardAutomatically(bool $awardAutomatically): static
    {
        $this->properties['awardAutomatically'] = (int)$awardAutomatically;

        return $this;
    }

    public function setRevokeAutomatically(bool $revokeAutomatically): static
    {
        $this->properties['revokeAutomatically'] = (int)$revokeAutomatically;

        return $this;
    }

    public function setTrophyUseHtml(bool $trophyUseHtml): static
    {
        $this->properties['trophyUseHtml'] = (int)$trophyUseHtml;

        return $this;
    }

    /**
     * Sets the position of the trophy, shifting the trophies that follow it.
     * A value of `0` appends the trophy at the end.
     */
    public function setShowOrder(int $showOrder): static
    {
        $this->showOrder = $showOrder;

        return $this;
    }

    /**
     * Sets the serialized object filters that a user must match to be awarded
     * automatically.
     */
    public function setConditions(?string $conditions): static
    {
        if ($conditions === '[]') {
            $conditions = null;
        }

        $this->properties['conditions'] = $conditions;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['categoryID', 'type'];
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        if (!isset($this->title)) {
            throw new \BadMethodCallException("Missing value for 'title'.");
        }
    }

    #[\Override]
    protected function afterCreate(): void
    {
        $this->saveL10nValues($this->getObject());

        if ($this->showOrder !== null) {
            self::applyShowOrder($this->getObject(), $this->showOrder);
        }
    }

    #[\Override]
    protected function afterUpdate(): void
    {
        $oldObject = $this->getExistingObject();

        if (isset($this->title) || isset($this->description)) {
            $this->saveL10nValues($this->getObject());
        }

        if ($this->showOrder !== null && $this->showOrder !== $oldObject->showOrder) {
            self::applyShowOrder($this->getObject(), $this->showOrder);
        }

        if (
            \array_key_exists('iconFileID', $this->properties)
            && $oldObject->iconFileID !== null
            && $oldObject->iconFileID !== $this->getObject()->iconFileID
        ) {
            $file = FileRuntimeCache::getInstance()->getObject($oldObject->iconFileID);
            if ($file !== null) {
                new DeleteFiles([$file])();
            }
        }
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        ConditionHandler::getInstance()->deleteConditions('com.woltlab.wcf.condition.trophy', $objectIDs);
    }

    private function saveL10nValues(Trophy $trophy): void
    {
        $description = $this->description ?? null;
        if ($description === null) {
            $description = $this->isUpdate()
                ? $this->getExistingObject()->getL10nValues('description')
                : [L10nStorage::MONOLINGUAL => ''];
        }

        // `L10nStorage::setValues()` replaces all rows of the trophy.
        (new L10nStorage(Trophy::getL10nDefinition()))->setValues(
            $trophy->trophyID,
            [
                'title' => $this->title ?? $this->getExistingObject()->getL10nValues('title'),
                'description' => $description,
            ]
        );
    }

    /**
     * Moves the trophy to the given position, shifting the trophies that follow
     * it. A value of `0` appends the trophy at the end.
     */
    private static function applyShowOrder(Trophy $trophy, int $showOrder): void
    {
        $sql = "SELECT  MAX(showOrder)
                FROM    wcf1_trophy";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $maxShowOrder = (int)$statement->fetchSingleColumn();

        if ($showOrder === 0 || $showOrder > $maxShowOrder) {
            $newShowOrder = $maxShowOrder + 1;
        } else {
            $sql = "UPDATE  wcf1_trophy
                    SET     showOrder = showOrder + 1
                    WHERE   showOrder >= ?
                        AND trophyID <> ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $showOrder,
                $trophy->trophyID,
            ]);

            $newShowOrder = $showOrder;
        }

        $sql = "UPDATE  wcf1_trophy
                SET     showOrder = ?
                WHERE   trophyID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $newShowOrder,
            $trophy->trophyID,
        ]);
    }

    public static function resetCache(): void
    {
        TrophyCache::getInstance()->clearCache();
        ConditionCacheBuilder::getInstance()->reset([
            'definitionID' => ObjectTypeCache::getInstance()
                ->getDefinitionByName('com.woltlab.wcf.condition.trophy')
                ->definitionID,
        ]);
    }
}
