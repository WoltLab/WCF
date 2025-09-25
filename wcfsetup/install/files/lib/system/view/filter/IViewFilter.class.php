<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;

/**
 * Represents a filter of a view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IViewFilter
{
    /**
     * Returns the form field for the input of this filter.
     */
    public function getFormField(): AbstractFormField;

    /**
     * Applies the filter to the given database object list.
     *
     * @param DatabaseObjectList<DatabaseObject> $list
     */
    public function applyFilter(DatabaseObjectList $list, string $value): void;

    /**
     * Renders the filter value in a human readable format.
     */
    public function renderValue(string $value): string;

    /**
     * Returns the id of this filter.
     */
    public function getId(): string;

    /**
     * Returns the label of this filter.
     */
    public function getLabel(): string;

    /**
     * Serializes the given form field value to a string that can be passed in a URL.
     */
    public function serializeValue(mixed $value): string;

    /**
     * Unserializes the given string to a value that can be passed to the form field.
     */
    public function unserializeValue(string $value): mixed;

    /**
     * Returns the id of the form field used by this filter.
     */
    public function getFormDataId(): string;
}
