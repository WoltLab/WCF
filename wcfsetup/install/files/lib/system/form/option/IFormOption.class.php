<?php

namespace wcf\system\form\option;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\database\table\column\AbstractDatabaseTableColumn;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\option\formatter\IFormOptionFormatter;

/**
 * Represents a form option type.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IFormOption
{
    /**
     * Returns the unique ID of this form field.
     */
    public function getId(): string;

    /**
     * Returns the title of this field that is being displayed to the user.
     */
    public function getTitle(): string;

    /**
     * Returns the form field that the user interacts with when entering a value.
     *
     * @param array<string, mixed> $configuration
     */
    public function getFormField(string $id, array $configuration = []): AbstractFormField;

    /**
     * Returns the form field tha the user interacts with when filtering the
     * values entered for this field.
     *
     * @param array<string, mixed> $configuration
     */
    public function getFilterFormField(string $id, array $configuration = []): AbstractFormField;

    /**
     * Returns a list of additional form fields that are part of configuring the
     * option itself. For example, an integer option would offer extra fields to
     * specifiy the minimum or maximum boundary of values.
     *
     * @return string[]
     */
    public function getConfigurationFormFields(): array;

    /**
     * Returns the formatter for a rich output.
     */
    public function getFormatter(): IFormOptionFormatter;

    /**
     * Returns a formatter that yields an output suitable for plaintext use
     * cases such as emails or HTML properties.
     */
    public function getPlainTextFormatter(): IFormOptionFormatter;

    /**
     * Applies conditions to the DatabaseObjectList to filter by the provided
     * value.
     *
     * @param DatabaseObjectList<DatabaseObject> $list
     */
    public function applyFilter(DatabaseObjectList $list, string $columnName, mixed $value): void;

    /**
     * Renders the value that is displayed for active filters of a list of grid
     * view.
     *
     * @param array<string, mixed> $configuration
     */
    public function renderFilterValue(string $value, array $configuration = []): string;

    /**
     * Returns the PHP DDL column definition to store the values.
     */
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn;
}
