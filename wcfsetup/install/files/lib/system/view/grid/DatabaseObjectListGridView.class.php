<?php

namespace wcf\system\view\grid;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;

abstract class DatabaseObjectListGridView extends AbstractGridView
{
    protected string $objectListClassName;
    protected DatabaseObjectList $objectList;
    private int $objectCount;

    protected function getRows(): array
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
        if (!isset($this->objectListClassName)) {
            throw new SystemException('Database object list class name not specified.');
        }

        if (!\is_subclass_of($this->objectListClassName, DatabaseObjectList::class)) {
            throw new ParentClassException($this->objectListClassName, DatabaseObjectList::class);
        }

        $this->objectList = new $this->objectListClassName;
        $this->objectList->sqlLimit = $this->getRowsPerPage();
        $this->objectList->sqlOffset = ($this->getPageNo() - 1) * $this->getRowsPerPage();
        //wcfDebug($this->objectList->sqlLimit, $this->objectList->sqlOffset);
        if ($this->getSortField()) {
            $this->objectList->sqlOrderBy = $this->getSortField() . ' ' . $this->getSortOrder();
        }
    }

    public function getObjectList(): DatabaseObjectList
    {
        if (!isset($this->objectList)) {
            $this->initObjectList();
        }

        return $this->objectList;
    }
}
