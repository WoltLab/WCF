<?php

namespace wcf\event\trophy;

use wcf\data\user\User;
use wcf\event\IPsr14Event;
use wcf\system\object\filter\IObjectListFilter;

/**
 * Requests the collection of the filters that are available for the conditions
 * of trophies that are awarded automatically.
 *
 * Only list filters are supported because the conditions are applied to the
 * conditions of a user list.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TrophyObjectFilterCollecting implements IPsr14Event
{
    /**
     * @var array<string, IObjectListFilter<User, mixed>>
     */
    private array $filters = [];

    /**
     * Registers a new filter. An existing filter with the same identifier is replaced.
     *
     * @param IObjectListFilter<User, mixed> $filter
     */
    public function register(IObjectListFilter $filter): void
    {
        $this->filters[$filter->getIdentifier()] = $filter;
    }

    /**
     * @return list<IObjectListFilter<User, mixed>>
     */
    public function getFilters(): array
    {
        return \array_values($this->filters);
    }
}
