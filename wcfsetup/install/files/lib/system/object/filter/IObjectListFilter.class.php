<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;

/**
 * Object filter that can additionally be expressed as an SQL condition,
 * allowing the filter to be applied to an entire object list at once.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject
 * @phpstan-template-contravariant TDatabaseObject of DatabaseObject
 * @template TValueType of mixed
 * @extends IObjectFilter<TDatabaseObject, TValueType>
 */
interface IObjectListFilter extends IObjectFilter
{
    /**
     * Adds the SQL condition that matches the objects satisfying the given
     * value to the given object list. Filters may add conditions and the joins
     * required by them, but must not change anything else.
     *
     * @param DatabaseObjectList<TDatabaseObject> $list
     * @param TValueType $value
     */
    public function applyFilter(DatabaseObjectList $list, mixed $value): void;
}
