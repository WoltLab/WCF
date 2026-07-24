<?php

namespace wcf\system\map;

use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Represents the geographic boundaries of a map viewport and builds the
 * matching SQL conditions to select the objects located within them.
 *
 * The longitude condition is aware of the international date line: when the
 * viewport crosses the antimeridian (`west > east`) the range is split into
 * two parts joined by `OR`. Undefined coordinates are expected to be stored
 * as `NULL`, therefore only rows with both coordinates set are matched. This
 * intentionally includes valid locations at the equator and the prime
 * meridian (latitude/longitude `0`).
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MapBoundingBox
{
    public function __construct(
        public readonly float $northLatitude,
        public readonly float $southLatitude,
        public readonly float $eastLongitude,
        public readonly float $westLongitude,
    ) {
        if ($this->northLatitude < -90 || $this->northLatitude > 90) {
            throw new \InvalidArgumentException("Invalid value for 'northLatitude' given.");
        }
        if ($this->southLatitude < -90 || $this->southLatitude > 90) {
            throw new \InvalidArgumentException("Invalid value for 'southLatitude' given.");
        }
        if ($this->eastLongitude < -180 || $this->eastLongitude > 180) {
            throw new \InvalidArgumentException("Invalid value for 'eastLongitude' given.");
        }
        if ($this->westLongitude < -180 || $this->westLongitude > 180) {
            throw new \InvalidArgumentException("Invalid value for 'westLongitude' given.");
        }
    }

    /**
     * Adds the conditions that restrict the results to the objects located
     * within these boundaries. The column names may be prefixed with a table
     * alias, e.g. `event.latitude`.
     */
    public function applyToConditionBuilder(
        PreparedStatementConditionBuilder $conditions,
        string $latitudeColumn = 'latitude',
        string $longitudeColumn = 'longitude'
    ): void {
        $conditions->add("{$latitudeColumn} IS NOT NULL");
        $conditions->add("{$longitudeColumn} IS NOT NULL");
        $conditions->add("{$latitudeColumn} <= ?", [$this->northLatitude]);
        $conditions->add("{$latitudeColumn} >= ?", [$this->southLatitude]);

        if ($this->westLongitude <= $this->eastLongitude) {
            $conditions->add("{$longitudeColumn} >= ?", [$this->westLongitude]);
            $conditions->add("{$longitudeColumn} <= ?", [$this->eastLongitude]);
        } else {
            // The viewport crosses the international date line, therefore the
            // longitude range wraps around ±180 and has to be split in two.
            $conditions->add(
                "({$longitudeColumn} >= ? OR {$longitudeColumn} <= ?)",
                [$this->westLongitude, $this->eastLongitude]
            );
        }
    }
}
