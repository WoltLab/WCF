<?php

namespace wcf\event\ad;

use wcf\data\user\User;
use wcf\event\IPsr14Event;
use wcf\system\object\filter\IObjectFilter;

/**
 * Requests the collection of the filters that are available for the conditions of ads.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class AdObjectFilterCollecting implements IPsr14Event
{
    /**
     * @var array<string, IObjectFilter<User, mixed>>
     */
    private array $filters = [];

    /**
     * Registers a new filter. An existing filter with the same identifier is replaced.
     *
     * @param IObjectFilter<User, mixed> $filter
     */
    public function register(IObjectFilter $filter): void
    {
        $this->filters[$filter->getIdentifier()] = $filter;
    }

    /**
     * @return list<IObjectFilter<User, mixed>>
     */
    public function getFilters(): array
    {
        return \array_values($this->filters);
    }
}
