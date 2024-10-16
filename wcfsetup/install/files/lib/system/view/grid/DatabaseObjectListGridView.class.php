<?php

namespace wcf\system\view\grid;

use LogicException;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;

abstract class DatabaseObjectListGridView extends AbstractGridView
{
    protected DatabaseObjectList $objectList;
    private int $objectCount;

    public function getRows(): array
    {
        $this->getObjectList()->readObjects();

        return $this->getObjectList()->getObjects();
    }

    public function countRows(): int
    {
        if (!isset($this->objectCount)) {
            $this->objectCount = $this->getObjectList()->countObjects();
        }

        return $this->objectCount;
    }

    protected function getData(mixed $row, string $identifer): mixed
    {
        \assert($row instanceof DatabaseObject);

        return $row->__get($identifer);
    }

    protected function initObjectList(): void
    {
        $this->objectList = $this->createObjectList();
        $this->objectList->sqlLimit = $this->getRowsPerPage();
        $this->objectList->sqlOffset = ($this->getPageNo() - 1) * $this->getRowsPerPage();
        if ($this->getSortField()) {
            $column = $this->getColumn($this->getSortField());
            if ($column && $column->getSortById()) {
                $this->objectList->sqlOrderBy = $column->getSortById() . ' ' . $this->getSortOrder();
            } else {
                $this->objectList->sqlOrderBy = $this->getSortField() . ' ' . $this->getSortOrder();
            }
        }
        $this->applyFilters();
    }

    public function getObjectList(): DatabaseObjectList
    {
        if (!isset($this->objectList)) {
            $this->initObjectList();
        }

        return $this->objectList;
    }

    private function applyFilters(): void
    {
        foreach ($this->getActiveFilters() as $key => $value) {
            $column = $this->getColumn($key);
            if (!$column) {
                throw new LogicException("Unknown column '" . $key . "'");
            }

            $column->getFilter()->applyFilter($this->getObjectList(), $column->getID(), $value);
        }
    }

    protected abstract function createObjectList(): DatabaseObjectList;
}
