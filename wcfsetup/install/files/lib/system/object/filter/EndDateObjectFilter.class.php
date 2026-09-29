<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;

/**
 * Filters by whether the current time is before the given point in time.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractTimestampObjectFilter<DatabaseObject>
 */
final class EndDateObjectFilter extends AbstractTimestampObjectFilter
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
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return \TIME_NOW < $configuredValue;
    }
}
