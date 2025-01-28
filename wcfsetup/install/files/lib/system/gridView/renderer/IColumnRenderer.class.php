<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;

/**
 * Represents a column renderer of a grid view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IColumnRenderer
{
    /**
     * Renders the content of a column with the given value.
     */
    public function render(mixed $value, DatabaseObject $row): string;

    /**
     * Returns the css classes of a column.
     */
    public function getClasses(): string;

    /**
     * Is called after the grid view data has been loaded and allows additional data to be loaded or cached.
     */
    public function prepare(mixed $value, DatabaseObject $row): void;
}
