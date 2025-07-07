<?php

namespace wcf\event\acp\dashboard\box;

use wcf\event\IPsr14Event;

/**
 * Requests the collection of objects that still need to be migrated
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class MigrationCollecting implements IPsr14Event
{
    /**
     * @var string[]
     */
    private array $needsMigration = [];

    /**
     * Adds the name of objects that still need to be migrated on the `RebuildDataPage`
     */
    public function migrationNeeded(string $title): void
    {
        $this->needsMigration[] = $title;
    }

    /**
     * @return string[]
     */
    public function needsMigration(): array
    {
        return $this->needsMigration;
    }
}
