<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;

/**
 * Filters by whether the current time is before the given point in time.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractTimestampObjectFilter<DatabaseObject>
 * @implements IObjectListFilter<DatabaseObject, int>
 */
final class EndDateObjectFilter extends AbstractTimestampObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.endDate';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.endDate';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, mixed $value): void
    {
        // The condition is added regardless of the result to prevent an empty
        // condition if this is the only filter.
        $list->getConditionBuilder()->add($this->testTime($value) ? '1=1' : '1=0');
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return $this->testTime($configuredValue);
    }

    private function testTime(int $value): bool
    {
        return \TIME_NOW < $value;
    }
}
