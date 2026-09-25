<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * @template TDatabaseObject of DatabaseObject
 * @template TValueType of mixed
 * @extends IObjectFilter<TDatabaseObject, TValueType>
 */
interface IObjectListFilter extends IObjectFilter
{
    /**
     * @param TValueType $value
     */
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void;
}
