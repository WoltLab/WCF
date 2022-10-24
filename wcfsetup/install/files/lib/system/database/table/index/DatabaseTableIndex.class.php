<?php

namespace wcf\system\database\table\index;

use wcf\system\database\table\TDroppableDatabaseComponent;

/**
 * Represents an index of a database table.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Index
 * @since   5.2
 */
final class DatabaseTableIndex
{
    use TDroppableDatabaseComponent;

    /**
     * indexed columns
     * @var string[]
     */
    private array $columns;

    /**
     * is `true` if index name has been automatically generated
     */
    private bool $generatedName = false;

    /**
     * name of index
     */
    private string $name;

    /**
     * type of index (see `*_TYPE` constants)
     */
    private ?string $type = null;

    const DEFAULT_TYPE = null;

    const PRIMARY_TYPE = 'PRIMARY';

    const UNIQUE_TYPE = 'UNIQUE';

    const FULLTEXT_TYPE = 'FULLTEXT';

    /**
     * Creates a new `DatabaseTableIndex` object.
     */
    private function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Sets the indexed columns and returns the index.
     *
     * @param string[] $columns indexed columns
     * @return  $this
     */
    public function columns(array $columns): static
    {
        $this->columns = \array_values($columns);

        return $this;
    }

    /**
     * Sets the automatically generated name of the index.
     *
     * @return  $this
     */
    public function generatedName(string $name): static
    {
        $this->name($name);
        $this->generatedName = true;

        return $this;
    }

    /**
     * Returns the index columns.
     *
     * @return  string[]
     */
    public function getColumns(): array
    {
        if (!isset($this->columns)) {
            throw new \BadMethodCallException(
                "Before getting the columns, they must be set for index '{$this->getName()}'."
            );
        }

        return $this->columns;
    }

    /**
     * Returns the data used by `DatabaseEditor` to add the index to a table.
     *
     * @return  array{columns: string, type: string}
     */
    public function getData(): array
    {
        return [
            'columns' => \implode(',', $this->getColumns()),
            'type' => $this->getType(),
        ];
    }

    /**
     * Returns the name of the index.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the type of the index (see `*_TYPE` constants).
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Returns `true` if the name of the index has been automatically generated.
     */
    public function hasGeneratedName(): bool
    {
        return $this->generatedName;
    }

    /**
     * Sets the name of the index.
     *
     * @return  $this
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the type of the index and returns the index
     *
     * @throws  \InvalidArgumentException   if given type is invalid
     */
    public function type(?string $type): static
    {
        if (
            $type !== static::DEFAULT_TYPE
            && $type !== static::PRIMARY_TYPE
            && $type !== static::UNIQUE_TYPE
            && $type !== static::FULLTEXT_TYPE
        ) {
            throw new \InvalidArgumentException("Unknown index type '{$type}'.");
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Returns a `DatabaseTableIndex` object with the given name.
     */
    public static function create(string $name): static
    {
        return new static($name);
    }

    /**
     * Returns a `DatabaseTableIndex` object with the given name and data.
     *
     * @param array $data data returned by `DatabaseEditor::getIndexInformation()`
     */
    public static function createFromData(string $name, array $data): static
    {
        return self::create($name)
            ->type($data['type'])
            ->columns($data['columns']);
    }
}
