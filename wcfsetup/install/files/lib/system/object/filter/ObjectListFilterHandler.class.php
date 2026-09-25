<?php

namespace wcf\system\object\filter;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\data\DatabaseObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * @template TDatabaseObject of DatabaseObject
 * @extends ObjectFilterHandler<TDatabaseObject>
 */
final class ObjectListFilterHandler extends ObjectFilterHandler
{
    /**
     * @param list<IObjectListFilter<TDatabaseObject, mixed>> $filters
     */
    public function __construct(
        array $filters,
    ) {
        parent::__construct($filters);
    }

    /**
     * @throws MappingError
     */
    public function applyFilters(PreparedStatementConditionBuilder $conditions, ?string $json): void
    {
        $values = $this->unserializeValues($json);
        if ($values === []) {
            $conditions->add('1=0');

            return;
        }

        $filters = $this->getFiltersByIdentifier();

        $hasActiveFilters = false;
        foreach ($values as [$identifier, $serializedValue]) {
            $filter = $filters[$identifier] ?? null;
            if ($filter === null) {
                continue;
            }

            \assert($filter instanceof IObjectListFilter);

            $filter->applyFilter(
                $conditions,
                $filter->unserializeValue($serializedValue),
            );
            $hasActiveFilters = true;
        }

        if (!$hasActiveFilters) {
            $conditions->add('1=0');
        }
    }
}
