<?php

namespace wcf\data\user\trophy;

use wcf\data\DatabaseObjectBuilder;
use wcf\system\l10n\L10nStorage;

/**
 * Builder for creating, updating and deleting user trophies.
 *
 * The localized custom description is stored in the `wcf1_user_trophy_l10n`
 * table (see `L10nStorage`).
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserTrophy>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class UserTrophyBuilder extends DatabaseObjectBuilder
{
    /**
     * @var L10nValue
     */
    private array $description;

    public function setTrophyID(int $trophyID): static
    {
        $this->properties['trophyID'] = $trophyID;

        return $this;
    }

    public function setUserID(int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets the time when the trophy was awarded.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    public function setUseCustomDescription(bool $useCustomDescription): static
    {
        $this->properties['useCustomDescription'] = (int)$useCustomDescription;

        return $this;
    }

    public function setTrophyUseHtml(bool $trophyUseHtml): static
    {
        $this->properties['trophyUseHtml'] = (int)$trophyUseHtml;

        return $this;
    }

    /**
     * Sets the custom description that is shown instead of the description of
     * the trophy if `setUseCustomDescription(true)` is used.
     *
     * @param L10nValue $description
     */
    public function setDescription(array $description): static
    {
        $this->description = $description;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['trophyID', 'userID', 'time'];
    }

    #[\Override]
    protected function afterCreate(): void
    {
        $this->saveL10nValues($this->getObject());
    }

    #[\Override]
    protected function afterUpdate(): void
    {
        $this->saveL10nValues($this->getObject());
    }

    private function saveL10nValues(UserTrophy $userTrophy): void
    {
        if (!isset($this->description)) {
            return;
        }

        // `L10nStorage::setValues()` replaces all rows of the user trophy.
        (new L10nStorage(UserTrophy::getL10nDefinition()))->setValues(
            $userTrophy->userTrophyID,
            ['description' => $this->description]
        );
    }
}
