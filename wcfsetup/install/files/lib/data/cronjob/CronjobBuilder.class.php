<?php

namespace wcf\data\cronjob;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Builder for creating and updating cronjobs.
 *
 * The localized description is stored in the `wcf1_cronjob_l10n` table (see
 * `L10nStorage`). Cronjobs created without a name, i.e. in the ACP, are
 * named `com.woltlab.wcf.cronjob<cronjobID>`.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Cronjob>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class CronjobBuilder extends DatabaseObjectBuilder
{
    /**
     * @var L10nValue
     */
    private array $description;

    private bool $generateCronjobName = false;

    /**
     * @param L10nValue $description
     */
    public function setDescription(array $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setClassName(string $className): static
    {
        $this->properties['className'] = $className;

        return $this;
    }

    public function setPackageID(int $packageID): static
    {
        $this->properties['packageID'] = $packageID;

        return $this;
    }

    public function setCronjobName(string $cronjobName): static
    {
        $this->properties['cronjobName'] = $cronjobName;

        return $this;
    }

    public function setStartMinute(string $startMinute): static
    {
        $this->properties['startMinute'] = $startMinute;

        return $this;
    }

    public function setStartHour(string $startHour): static
    {
        $this->properties['startHour'] = $startHour;

        return $this;
    }

    public function setStartDom(string $startDom): static
    {
        $this->properties['startDom'] = $startDom;

        return $this;
    }

    public function setStartMonth(string $startMonth): static
    {
        $this->properties['startMonth'] = $startMonth;

        return $this;
    }

    public function setStartDow(string $startDow): static
    {
        $this->properties['startDow'] = $startDow;

        return $this;
    }

    public function setNextExec(int $nextExec): static
    {
        $this->properties['nextExec'] = $nextExec;

        return $this;
    }

    public function setAfterNextExec(int $afterNextExec): static
    {
        $this->properties['afterNextExec'] = $afterNextExec;

        return $this;
    }

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = $isDisabled ? 1 : 0;

        return $this;
    }

    public function setCanBeEdited(bool $canBeEdited): static
    {
        $this->properties['canBeEdited'] = $canBeEdited ? 1 : 0;

        return $this;
    }

    public function setCanBeDisabled(bool $canBeDisabled): static
    {
        $this->properties['canBeDisabled'] = $canBeDisabled ? 1 : 0;

        return $this;
    }

    /**
     * @param string $options comma separated list of options, see `TDatabaseObjectOptions`
     */
    public function setOptions(string $options): static
    {
        $this->properties['options'] = $options;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['className', 'packageID'];
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        if (!isset($this->description)) {
            throw new \BadMethodCallException("Missing value for 'description'.");
        }

        if (!isset($this->properties['cronjobName'])) {
            // The final name requires the cronjob's id, see `afterCreate()`. A
            // unique placeholder avoids collisions on the unique key
            // `(cronjobName, packageID)` between concurrent creations.
            $this->properties['cronjobName'] = 'com.woltlab.wcf.cronjob.' . StringUtil::getRandomID();
            $this->generateCronjobName = true;
        }
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        if ($this->generateCronjobName) {
            $sql = "UPDATE  wcf1_cronjob
                    SET     cronjobName = ?
                    WHERE   cronjobID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                'com.woltlab.wcf.cronjob' . $object->cronjobID,
                $object->cronjobID,
            ]);
        }

        $this->saveL10nValues($object);
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if (isset($this->description)) {
            $this->saveL10nValues($object);
        }

        $this->rescheduleOnExpressionChange($object);
    }

    /**
     * Recalculates the next execution if the schedule has changed, otherwise
     * the previous schedule would apply until the cronjob is executed again.
     */
    private function rescheduleOnExpressionChange(Cronjob $cronjob): void
    {
        $previousExpression = $this->getObject()->getExpression()->getExpression();
        if ($cronjob->getExpression()->getExpression() === $previousExpression) {
            return;
        }

        $nextExec = $cronjob->getNextExec(\TIME_NOW);
        $builder = self::forUpdate($cronjob)
            ->setNextExec($nextExec);

        // The `afterNextExec` of a pending or executing cronjob is used by
        // `CronjobScheduler` to detect crashes and must remain untouched.
        if ($cronjob->state === Cronjob::READY) {
            // Offset taken from `CronjobScheduler`
            $builder->setAfterNextExec($cronjob->getNextExec($nextExec + 120));
        }

        $builder->update();
    }

    private function saveL10nValues(Cronjob $cronjob): void
    {
        (new L10nStorage(Cronjob::getL10nDefinition()))->setValues(
            $cronjob->cronjobID,
            [
                'description' => $this->description,
            ]
        );
    }
}
