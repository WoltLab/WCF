<?php

namespace wcf\system\object\filter\builder;

use wcf\data\DatabaseObject;
use wcf\system\object\filter\IObjectFilter;

/**
 * Provides the set of filters that can be configured for a specific use case,
 * e.g. the conditions of notices or automatic user group assignments.
 *
 * Builders are registered through the `ObjectFilterBuilderCollecting` event
 * and are addressed by their identifier when the filter dialog is requested.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject
 */
interface IObjectFilterBuilder
{
    /**
     * Returns the filters that are available for this builder.
     *
     * @return list<IObjectFilter<TDatabaseObject, mixed>>
     */
    public function getFilters(): array;

    /**
     * Returns the unique identifier of this builder, e.g. `com.woltlab.wcf.userGroupAssignment`.
     */
    public function getIdentifier(): string;

    /**
     * Returns true if this builder is accessible for the active user.
     */
    public function isAccessible(): bool;
}
