<?php

namespace wcf\system\object\filter;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;

/**
 * Evaluates stored filter values that consist exclusively of list filters,
 * allowing them to be applied to the conditions of an object list in
 * addition to testing individual objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
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
     * Adds the SQL conditions of all stored filter values to the given object
     * list. Values of unknown filters are ignored.
     *
     * Adds a condition that matches no objects if there are no active filters.
     *
     * @param DatabaseObjectList<TDatabaseObject> $list
     * @throws MappingError if the stored filter values are malformed
     */
    public function applyFilters(DatabaseObjectList $list, ?string $json): void
    {
        $values = $this->unserializeValues($json);
        if ($values === []) {
            $list->getConditionBuilder()->add('1=0');

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
                $list,
                $filter->unserializeValue($serializedValue),
            );
            $hasActiveFilters = true;
        }

        if (!$hasActiveFilters) {
            $list->getConditionBuilder()->add('1=0');
        }
    }
}
