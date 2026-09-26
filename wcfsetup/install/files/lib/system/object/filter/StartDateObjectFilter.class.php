<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;

/**
 * Filters by whether the current time is at or after the given point in time.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractTimestampObjectFilter<DatabaseObject>
 */
final class StartDateObjectFilter extends AbstractTimestampObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.startDate';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.startDate';
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return \TIME_NOW >= $configuredValue;
    }
}
