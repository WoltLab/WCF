<?php

namespace wcf\system\object\filter\builder;

use wcf\data\DatabaseObject;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\object\filter\ObjectFilterHandler;

/**
 * Default implementation of an object filter builder that lazily creates its
 * filters and the handler that evaluates the stored filter values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
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
     * Creates the filters that are available for this builder. This method is
     * called at most once, the result is cached by `getFilters()`.
     *
     * @return list<TFilter>
     */
    abstract protected function createFilters(): array;

    /**
     * Creates the handler that evaluates the stored filter values using the
     * given filters. This method is called at most once, the result is cached
     * by `getHandler()`.
     *
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
     * Returns the handler that evaluates the stored filter values.
     *
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
