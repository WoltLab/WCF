<?php

namespace wcf\system\database\table\index;

use wcf\system\application\ApplicationHandler;
use wcf\system\database\table\TDroppableDatabaseComponent;

/**
 * Represents a foreign key of a database table.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class DatabaseTableForeignKey
{
    use TDroppableDatabaseComponent;

    /**
     * columns affected by the foreign key
     * @var string[]
     */
    protected array $columns;

    /**
     * name of the foreign key
     * @var string
     */
    protected string $name;

    /**
     * action executed in referenced table if row is deleted
     */
    protected ?string $onDelete = null;

    /**
     * action executed in referenced table if row is updated
     */
    protected ?string $onUpdate = null;

    /**
     * relevant columns in referenced table
     * @var string[]
     */
    protected array $referencedColumns;

    /**
     * name of referenced table
     */
    protected string $referencedTable;

    /**
     * valid on delete/update actions
     * @var string[]
     */
    public const VALID_ACTIONS = [
        'CASCADE',
        'NO ACTION',
        'SET NULL',
    ];

    /**
     * @param string $name column name
     */
    protected function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Sets the columns affected by the foreign key and returns the foreign key.
     *
     * @param string[] $columns columns affected by foreign key
     * @return $this this foreign key
     */
    public function columns(array $columns): self
    {
        $this->columns = \array_values($columns);

        return $this;
    }

    /**
     * Returns the name of the foreign key.
     *
     * If the key belongs to a database table layout not created from an existing database table,
     * the name might be empty.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the columns affected by the foreign key
     *
     * @return string[] columns affected by foreign key
     * @throws \BadMethodCallException if not columns have been set
     */
    public function getColumns(): array
    {
        if (!isset($this->columns)) {
            throw new \BadMethodCallException(
                "Before getting the columns, they must be set for foreign key '{$this->getName()}'."
            );
        }

        return $this->columns;
    }

    /**
     * Returns the data used by `DatabaseEditor` to add the foreign key to a table.
     *
     * @return array{
     *  columns: string,
     *  referencedColumns: string,
     *  referencedTable: string,
     *  'ON DELETE': ?string,
     *  'ON UPDATE': ?string,
     * }
     */
    public function getData(): array
    {
        return [
            'columns' => \implode(',', $this->getColumns()),
            'ON DELETE' => $this->normalizeAction($this->getOnDelete()),
            'ON UPDATE' => $this->normalizeAction($this->getOnUpdate()),
            'referencedColumns' => \implode(',', $this->getReferencedColumns()),
            'referencedTable' => $this->getReferencedTable(),
        ];
    }

    /**
     * Returns the data used to compare foreign keys.
     *
     * @return array{columns: string, referencedColumns: string, referencedTable: string}
     */
    public function getDiffData(): array
    {
        return [
            'columns' => \implode(',', $this->getColumns()),
            'referencedColumns' => \strtolower(\implode(',', $this->getReferencedColumns())),
            'referencedTable' => $this->getReferencedTable(),
        ];
    }

    /**
     * Returns the action executed in referenced table if row is deleted or `null` if no such
     * action has been set.
     */
    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }

    /**
     * Returns the action executed in referenced table if row is updated or `null` if no such
     * action has been set.
     */
    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }

    /**
     * Returns the relevant columns in referenced table.
     *
     * @return string[]
     * @throws \BadMethodCallException if referenced columns have not been set
     */
    public function getReferencedColumns(): array
    {
        if (!isset($this->referencedColumns)) {
            throw new \BadMethodCallException(
                "Before getting the referenced columns, they must be set for foreign key '{$this->getName()}'."
            );
        }

        return $this->referencedColumns;
    }

    /**
     * Returns the name of the referenced table.
     *
     * @throws \BadMethodCallException if referenced table has not been set
     */
    public function getReferencedTable(): string
    {
        if (!isset($this->referencedTable)) {
            throw new \BadMethodCallException(
                "Before getting the referenced table, it must be set for foreign key '{$this->getName()}'."
            );
        }

        return $this->referencedTable;
    }

    /**
     * Sets the name of the foreign key.
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the action executed in referenced table if row is deleted and returns the foreign
     * key.
     *
     * @param ?string $onDelete action executed in referenced table if row is deleted
     * @return $this this foreign key
     * @throws \InvalidArgumentException if given action is invalid
     */
    public function onDelete(?string $onDelete): self
    {
        if ($onDelete !== null && !\in_array($onDelete, static::VALID_ACTIONS)) {
            throw new \InvalidArgumentException("Unknown on delete action '{$onDelete}'.");
        }

        $this->onDelete = $onDelete;

        return $this;
    }

    /**
     * Sets the action executed in referenced table if row is updated and returns the foreign
     * key.
     *
     * @param ?string $onUpdate action executed in referenced table if row is updated
     * @throws \InvalidArgumentException if given action is invalid
     */
    public function onUpdate(?string $onUpdate): self
    {
        if ($onUpdate !== null && !\in_array($onUpdate, static::VALID_ACTIONS)) {
            throw new \InvalidArgumentException("Unknown on update action '{$onUpdate}'.");
        }

        $this->onUpdate = $onUpdate;

        return $this;
    }

    /**
     * Sets the relevant columns of the referenced table and returns the foreign key.
     *
     * @param string[] $referencedColumns columns of referenced table
     */
    public function referencedColumns(array $referencedColumns): self
    {
        $this->referencedColumns = $referencedColumns;

        return $this;
    }

    /**
     * Sets the name of the referenced table and returns the foreign key.
     */
    public function referencedTable(string $referencedTable): self
    {
        $this->referencedTable = ApplicationHandler::insertRealDatabaseTableNames($referencedTable, true);

        return $this;
    }

    /**
     * In MySQL, `ON * RESTRICT`, `ON * NO ACTION` or omitting it entirely, is completely the same. However,
     * MySQL 8 reports `NO ACTION` where MySQL 5.7 would identify the action as `null`. This method normalized
     * the action, by always setting it to null if the value is `RESTRICT` or `NO ACTION`.
     */
    protected function normalizeAction(?string $action): ?string
    {
        if ($action === null || $action === 'RESTRICT' || $action === 'NO ACTION') {
            return null;
        }

        return $action;
    }

    /**
     * Returns a `DatabaseTableForeignKey` object with the given name.
     */
    public static function create(string $name = ''): self
    {
        return new static($name);
    }

    /**
     * Returns a `DatabaseTableForeignKey` object with the given name and data.
     *
     * @param array{
     *  columns: string[],
     *  referencedTable: string,
     *  referencedColumns: string[],
     *  'ON DELETE'?: string,
     *  'ON UPDATE': string,
     * } $data data returned by `DatabaseEditor::getForeignKeys()`
     */
    public static function createFromData(string $name, array $data): self
    {
        return static::create($name)
            ->columns($data['columns'])
            ->onDelete($data['ON DELETE'])
            ->onUpdate($data['ON UPDATE'])
            ->referencedColumns($data['referencedColumns'])
            ->referencedTable($data['referencedTable']);
    }
}
