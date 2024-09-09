<?php

namespace wcf\system\view\grid;

use wcf\system\WCF;

abstract class AbstractGridView
{
    private array $columns = [];
    private int $rowsPerPage = 2;
    private string $baseUrl = '';

    public function __construct(private readonly int $pageNo = 1)
    {
        $this->init();
    }

    protected function init(): void {}

    public function addColumn(GridViewColumn $column): void
    {
        $this->columns[] = $column;
    }

    /**
     * @param GridViewColumn[] $columns
     */
    public function addColumns(array $columns): void
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
    }

    /**
     * @return GridViewColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function render(): string
    {
        return WCF::getTPL()->fetch('shared_gridView', 'wcf', ['view' => $this], true);
    }

    public function renderHeader(): string
    {
        $header = '';

        foreach ($this->getColumns() as $column) {
            $header .= <<<EOT
                <th class="{$column->getClasses()}">{$column->getLabel()}</th>
            EOT;
        }

        return $header;
    }

    public function renderRows(): string
    {
        $result = '';

        foreach ($this->getRows($this->rowsPerPage, ($this->pageNo - 1) * $this->rowsPerPage) as $row) {
            $result .= <<<EOT
                <tr>
            EOT;

            foreach ($this->getColumns() as $column) {
                $result .= <<<EOT
                    <td class="{$column->getClasses()}">
                        {$column->render($this->getData($row,$column->getID()),$row)}
                    </td>
                EOT;
            }

            $result .= <<<EOT
                </tr>
            EOT;
        }

        return $result;
    }

    protected function getData(mixed $row, string $identifer): mixed
    {
        return $row[$identifer] ?? '';
    }

    public abstract function getRows(int $limit, int $offset = 0): array;


    public function getPageNo(): int
    {
        return $this->pageNo;
    }

    public function countPages(): int
    {
        return 3;
    }

    public function getClassName(): string
    {
        return \get_class($this);
    }

    public function isAccessible(): bool
    {
        return true;
    }

    public function getID(): string
    {
        $classNamePieces = \explode('\\', static::class);

        return \implode('-', $classNamePieces);
    }

    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
