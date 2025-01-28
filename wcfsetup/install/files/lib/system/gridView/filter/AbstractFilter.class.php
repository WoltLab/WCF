<?php

namespace wcf\system\gridView\filter;

use wcf\data\DatabaseObjectList;

/**
 * Abstract implementation for grid view column filters.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
abstract class AbstractFilter implements IGridViewFilter
{
    public function __construct(protected readonly string $databaseColumn = '') {}

    #[\Override]
    public function renderValue(string $value): string
    {
        return $value;
    }

    protected function getDatabaseColumnName(DatabaseObjectList $list, string $id): string
    {
        return ($this->databaseColumn ?: $list->getDatabaseTableAlias() . '.' . $id);
    }
}
