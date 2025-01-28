<?php

namespace wcf\system\gridView\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;

/**
 * Represents a filter of a grid view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IGridViewFilter
{
    /**
     * Returns the form field for the input of this filter.
     */
    public function getFormField(string $id, string $label): AbstractFormField;

    /**
     * Applies the filter to the given database object list.
     */
    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void;

    /**
     * Renders the filter value in a human readable format.
     */
    public function renderValue(string $value): string;
}
