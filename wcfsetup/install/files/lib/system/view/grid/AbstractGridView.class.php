<?php

namespace wcf\system\view\grid;

use wcf\system\WCF;

abstract class AbstractGridView
{
    private array $columns = [];
    private int $rowsPerPage = 2;
    private string $baseUrl = '';
    private string $sortField = '';
    private string $sortOrder = 'ASC';
    private int $pageNo = 1;

    public function __construct()
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

    public function renderRows(): string
    {
        return WCF::getTPL()->fetch('shared_gridViewRows', 'wcf', ['view' => $this], true);
    }

    public function renderColumn(GridViewColumn $column, mixed $row): string
    {
        return $column->render($this->getData($row, $column->getID()), $row);
    }

    protected function getData(mixed $row, string $identifer): mixed
    {
        return $row[$identifer] ?? '';
    }

    public abstract function getRows(): array;

    public abstract function countRows(): int;

    public function countPages(): int
    {
        return \ceil($this->countRows() / $this->getRowsPerPage());
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

    /**
     * @return GridViewColumn[]
     */
    public function getSortableColumns(): array
    {
        return \array_filter($this->getColumns(), fn($column) => $column->isSortable());
    }

    public function setSortField(string $sortField): void
    {
        if (!\in_array($sortField, \array_map(fn($column) => $column->getID(), $this->getSortableColumns()))) {
            throw new \InvalidArgumentException("Invalid value '{$sortField}' as sort field given.");
        }

        $this->sortField = $sortField;
    }

    public function setSortOrder(string $sortOrder): void
    {
        if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
            throw new \InvalidArgumentException("Invalid value '{$sortOrder}' as sort order given.");
        }

        $this->sortOrder = $sortOrder;
    }

    public function getSortField(): string
    {
        return $this->sortField;
    }

    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }

    public function getPageNo(): int
    {
        return $this->pageNo;
    }

    public function setPageNo(int $pageNo): void
    {
        $this->pageNo = $pageNo;
    }

    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }

    public function setRowsPerPage(int $rowsPerPage): void
    {
        $this->rowsPerPage = $rowsPerPage;
    }
}
