<?php

namespace wcf\data\blacklist\status;

use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating, updating and deleting blacklist status.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<BlacklistStatus>
 */
final class BlacklistStatusBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the ISO 8601 date (UTC) the status belongs to.
     *
     * @throws \InvalidArgumentException if the given value is not a valid `Y-m-d` date
     */
    public function setDate(string $date): static
    {
        $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, new \DateTimeZone('UTC'));
        if ($dateTime === false || $dateTime->format('Y-m-d') !== $date) {
            throw new \InvalidArgumentException("Invalid date '{$date}' given.");
        }

        return $this->setID($date);
    }

    /**
     * Marks the delta identified by its name, e.g. `delta1`, as fetched.
     *
     * @throws \InvalidArgumentException if the given delta is unknown
     */
    public function setDelta(string $delta, bool $fetched = true): static
    {
        if (!\in_array($delta, BlacklistStatus::DELTAS, true)) {
            throw new \InvalidArgumentException("Invalid delta '{$delta}' given.");
        }

        $this->properties[$delta] = $fetched ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['date'];
    }
}
