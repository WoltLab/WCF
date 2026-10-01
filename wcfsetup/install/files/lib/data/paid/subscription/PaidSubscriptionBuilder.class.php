<?php

namespace wcf\data\paid\subscription;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Builder for creating and updating paid subscriptions.
 *
 * The localized title and description are stored in the
 * `wcf1_paid_subscription_l10n` table (see `L10nStorage`).
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<PaidSubscription>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class PaidSubscriptionBuilder extends DatabaseObjectBuilder
{
    /**
     * @var L10nValue
     */
    private array $title;

    /**
     * @var L10nValue
     */
    private array $description;

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

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = $isDisabled ? 1 : 0;

        return $this;
    }

    /**
     * Sets the position relative to the other subscriptions, starting at `1`.
     * `0` or a position after the last subscription appends it.
     */
    public function setShowOrder(int $showOrder): static
    {
        $this->properties['showOrder'] = $showOrder;

        return $this;
    }

    public function setPrice(float $cost, string $currency): static
    {
        $this->properties['cost'] = $cost;
        $this->properties['currency'] = $currency;

        return $this;
    }

    /**
     * Marks the subscription as permanent, i.e. without an expiration.
     */
    public function setPermanent(): static
    {
        $this->properties['subscriptionLength'] = 0;
        $this->properties['subscriptionLengthUnit'] = '';
        $this->properties['isRecurring'] = 0;

        return $this;
    }

    /**
     * Sets the duration, `$unit` is one of `D` (days), `M` (months) or `Y` (years).
     */
    public function setLength(int $length, string $unit, bool $isRecurring): static
    {
        if ($length < 1) {
            throw new \InvalidArgumentException("The length must be at least 1, use setPermanent() instead.");
        }
        if (!\in_array($unit, ['D', 'M', 'Y'], true)) {
            throw new \InvalidArgumentException("The unit must be one of 'D', 'M' or 'Y'.");
        }

        $this->properties['subscriptionLength'] = $length;
        $this->properties['subscriptionLengthUnit'] = $unit;
        $this->properties['isRecurring'] = $isRecurring ? 1 : 0;

        return $this;
    }

    /**
     * @param list<int> $groupIDs
     */
    public function setGroupIDs(array $groupIDs): static
    {
        $this->properties['groupIDs'] = \implode(',', $groupIDs);

        return $this;
    }

    /**
     * @param list<int> $excludedSubscriptionIDs
     */
    public function setExcludedSubscriptionIDs(array $excludedSubscriptionIDs): static
    {
        $this->properties['excludedSubscriptionIDs'] = \implode(',', $excludedSubscriptionIDs);

        return $this;
    }

    #[\Override]
    protected function allowEmptyCreate(): bool
    {
        return true;
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        if (!isset($this->title)) {
            throw new \BadMethodCallException("Missing value for 'title'.");
        }
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        $showOrder = $this->properties['showOrder'] ?? 0;
        $maxShowOrder = $this->getMaxShowOrder($object->subscriptionID);
        if ($showOrder === 0 || $showOrder > $maxShowOrder) {
            $this->updateShowOrder($object->subscriptionID, $maxShowOrder + 1);
        } else {
            $this->shiftShowOrder($object->subscriptionID, $showOrder, 1);
        }

        $this->saveL10nValues($object);
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        $oldObject = $this->getObject();

        if (isset($this->properties['showOrder']) && $this->properties['showOrder'] !== $oldObject->showOrder) {
            // Closes the gap at the previous position before making room at
            // the new one, the position is relative to the other subscriptions.
            $this->shiftShowOrder($object->subscriptionID, $oldObject->showOrder + 1, -1);

            $showOrder = $this->properties['showOrder'];
            $maxShowOrder = $this->getMaxShowOrder($object->subscriptionID);
            if ($showOrder === 0 || $showOrder > $maxShowOrder) {
                $this->updateShowOrder($object->subscriptionID, $maxShowOrder + 1);
            } else {
                $this->shiftShowOrder($object->subscriptionID, $showOrder, 1);
            }
        }

        if (isset($this->title) || isset($this->description)) {
            $this->saveL10nValues($object);
        }
    }

    private function getMaxShowOrder(int $excludedSubscriptionID): int
    {
        $sql = "SELECT  COALESCE(MAX(showOrder), 0)
                FROM    wcf1_paid_subscription
                WHERE   subscriptionID <> ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$excludedSubscriptionID]);

        return (int)$statement->fetchSingleColumn();
    }

    private function updateShowOrder(int $subscriptionID, int $showOrder): void
    {
        $sql = "UPDATE  wcf1_paid_subscription
                SET     showOrder = ?
                WHERE   subscriptionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $showOrder,
            $subscriptionID,
        ]);
    }

    /**
     * Moves the other subscriptions at or after the given position by `$offset`.
     */
    private function shiftShowOrder(int $excludedSubscriptionID, int $fromShowOrder, int $offset): void
    {
        $sql = "UPDATE  wcf1_paid_subscription
                SET     showOrder = showOrder + ?
                WHERE   showOrder >= ?
                    AND subscriptionID <> ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $offset,
            $fromShowOrder,
            $excludedSubscriptionID,
        ]);
    }

    private function saveL10nValues(PaidSubscription $subscription): void
    {
        $description = $this->description ?? null;
        if ($description === null) {
            $description = $this->isUpdate()
                ? $this->getObject()->getL10nValues('description')
                : [L10nStorage::MONOLINGUAL => ''];
        }

        // `L10nStorage::setValues()` replaces all rows of the subscription.
        (new L10nStorage(PaidSubscription::getL10nDefinition()))->setValues(
            $subscription->subscriptionID,
            [
                'title' => $this->title ?? $this->getObject()->getL10nValues('title'),
                'description' => $description,
            ]
        );
    }
}
