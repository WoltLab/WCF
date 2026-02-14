<?php

namespace wcf\system\gridView;

use wcf\data\DatabaseObject;

/**
 * Represents a row link of a grid view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IGridViewRowLink
{
    /**
     * Renders the row link.
     */
    public function render(mixed $value, DatabaseObject $row, bool $isPrimaryColumn = false): string;

    /**
     * Renders the initialization code for this row link.
     */
    public function renderInitialization(string $containerId): ?string;

    /**
     * Returns true if the row link is available for the given row.
     */
    public function isAvailable(DatabaseObject $row): bool;
}
