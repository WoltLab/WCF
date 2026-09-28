<?php

namespace wcf\data\user\rank;

use wcf\command\file\DeleteFiles;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\l10n\L10nStorage;

/**
 * Builder for creating and updating user ranks.
 *
 * The localized title is stored in the `wcf1_user_rank_l10n` table (see
 * `L10nStorage`). `l10nIdentifier` links a user rank shipped with the package
 * to its language variable; it is `NULL` for user ranks created by an
 * administrator.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserRank>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class UserRankBuilder extends DatabaseObjectBuilder
{
    /**
     * @var L10nValue
     */
    private array $rankTitle;

    /**
     * @param L10nValue $rankTitle
     */
    public function setRankTitle(array $rankTitle): static
    {
        $this->rankTitle = $rankTitle;

        return $this;
    }

    public function setL10nIdentifier(?string $l10nIdentifier): static
    {
        $this->properties['l10nIdentifier'] = $l10nIdentifier;

        return $this;
    }

    public function setGroupID(int $groupID): static
    {
        $this->properties['groupID'] = $groupID;

        return $this;
    }

    public function setRequiredPoints(int $requiredPoints): static
    {
        $this->properties['requiredPoints'] = $requiredPoints;

        return $this;
    }

    /**
     * @param int $requiredGender see `UserProfile::GENDER_*`, `0` if no specific gender is required
     */
    public function setRequiredGender(int $requiredGender): static
    {
        $this->properties['requiredGender'] = $requiredGender;

        return $this;
    }

    public function setCssClassName(string $cssClassName): static
    {
        $this->properties['cssClassName'] = $cssClassName;

        return $this;
    }

    public function setRankImageFileID(?int $rankImageFileID): static
    {
        $this->properties['rankImageFileID'] = $rankImageFileID;

        return $this;
    }

    public function setRepeatImage(int $repeatImage): static
    {
        $this->properties['repeatImage'] = $repeatImage;

        return $this;
    }

    public function setHideTitle(bool $hideTitle): static
    {
        $this->properties['hideTitle'] = $hideTitle ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['groupID'];
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        // Shipped user ranks receive their title from the language variable
        // through `SyncL10nLanguageItems`.
        if (!isset($this->rankTitle) && ($this->properties['l10nIdentifier'] ?? null) === null) {
            throw new \BadMethodCallException("Missing value for 'rankTitle'.");
        }
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        if (isset($this->rankTitle)) {
            $this->saveL10nValues($object);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if (isset($this->rankTitle)) {
            $this->saveL10nValues($object);
        }

        // A user rank owns its image file, a replaced file is orphaned.
        $oldObject = $this->getObject();
        if (
            \array_key_exists('rankImageFileID', $this->properties)
            && $oldObject->rankImageFileID !== null
            && $oldObject->rankImageFileID !== $object->rankImageFileID
        ) {
            $file = FileRuntimeCache::getInstance()->getObject($oldObject->rankImageFileID);
            if ($file !== null) {
                new DeleteFiles([$file])();
            }
        }
    }

    private function saveL10nValues(UserRank $rank): void
    {
        (new L10nStorage(UserRank::getL10nDefinition()))->setValues(
            $rank->rankID,
            [
                'rankTitle' => $this->rankTitle,
            ]
        );
    }
}
