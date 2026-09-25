<?php

namespace wcf\system\object\filter\builder;

use wcf\data\DatabaseObject;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\object\filter\ObjectFilterHandler;

/**
 * @template TDatabaseObject of DatabaseObject
 * @template TFilter of IObjectFilter<TDatabaseObject, mixed>
 * @template THandler of ObjectFilterHandler<TDatabaseObject>
 * @implements IObjectFilterBuilder<TDatabaseObject>
 */
abstract class AbstractObjectFilterBuilder implements IObjectFilterBuilder
{
    /**
     * @var list<TFilter>
     */
    private array $filters;

    /**
     * @var THandler
     */
    private ObjectFilterHandler $handler;

    /**
     * @return list<TFilter>
     */
    abstract protected function createFilters(): array;

    /**
     * @param list<TFilter> $filters
     * @return THandler
     */
    abstract protected function createHandler(array $filters): ObjectFilterHandler;

    /**
     * @return list<TFilter>
     */
    #[\Override]
    public function getFilters(): array
    {
        if (!isset($this->filters)) {
            $this->filters = $this->createFilters();
        }

        return $this->filters;
    }

    /**
     * @return THandler
     */
    protected function getHandler(): ObjectFilterHandler
    {
        if (!isset($this->handler)) {
            $this->handler = $this->createHandler($this->getFilters());
        }

        return $this->handler;
    }
}
