<?php

namespace wcf\system\object\filter\builder;

use wcf\data\DatabaseObject;
use wcf\system\object\filter\IObjectFilter;

/**
 * @template TDatabaseObject of DatabaseObject
 */
interface IObjectFilterBuilder
{
    /**
     * @return list<IObjectFilter<TDatabaseObject, mixed>>
     */
    public function getFilters(): array;

    // `com.woltlab.wcf.userGroupAssignment`
    public function getIdentifier(): string;

    /**
     * Returns true if this builder is accessible for the active user.
     */
    public function isAccessible(): bool;
}
