<?php

namespace wcf\system\l10n;

use wcf\data\DatabaseObject;
use wcf\system\database\table\column\IDatabaseTableColumn;

/**
 * Describes the localizable payload columns of a database object. The payload
 * columns are stored in a separate table named after the database object's
 * table with the suffix `_l10n`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class L10nDefinition
{
    /**
     * @param class-string<DatabaseObject> $class
     * @param list<IDatabaseTableColumn> $columns
     */
    public function __construct(
        public readonly string $class,
        public readonly array $columns,
    ) {
        if (!\is_subclass_of($this->class, DatabaseObject::class)) {
            throw new \InvalidArgumentException(
                "Given class '{$this->class}' is no subclass of '" . DatabaseObject::class . "'."
            );
        }

        $columnNames = [];
        foreach ($this->columns as $column) {
            $name = \strtolower($column->getName());
            if ($name === 'objectid' || $name === 'languageid') {
                throw new \InvalidArgumentException(
                    "The column name '{$column->getName()}' is reserved for the default columns."
                );
            }

            if (isset($columnNames[$name])) {
                throw new \InvalidArgumentException("Duplicate column with name '{$column->getName()}'.");
            }
            $columnNames[$name] = true;
        }
    }

    public function getBaseTableName(): string
    {
        return $this->class::getDatabaseTableName();
    }

    public function getL10nTableName(): string
    {
        return $this->getBaseTableName() . '_l10n';
    }

    public function getBaseTableIndexName(): string
    {
        return $this->class::getDatabaseTableIndexName();
    }
}
