<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\tolerant\SortedUserCache;

/**
 * Caches a list of the newest members.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.2 use `SortedUserCache` instead
 */
abstract class AbstractSortedUserCacheBuilder extends AbstractLegacyCacheBuilder
{
    /**
     * default limit value if no limit parameter is provided
     * @var int
     */
    protected $defaultLimit = 5;

    /**
     * default sort order if no sort order parameter is provided
     * @var string
     */
    protected $defaultSortOrder = 'DESC';

    /**
     * if `true`, only positive values of the database column will be considered
     * @var bool
     */
    protected $positiveValuesOnly = false;

    /**
     * database table column used for sorting
     * @var string
     */
    protected $sortField;

    #[\Override]
    public function reset(array $parameters = [])
    {
        (new SortedUserCache(
            $this->sortField,
            $parameters['sortOrder'] ?? $this->defaultSortOrder,
            $parameters['limit'] ?? $this->defaultLimit,
            $this->positiveValuesOnly,
            $parameters['conditions'] ?? []
        ))->rebuild();
    }

    #[\Override]
    protected function rebuild(array $parameters): array
    {
        return (new SortedUserCache(
            $this->sortField,
            $parameters['sortOrder'] ?? $this->defaultSortOrder,
            $parameters['limit'] ?? $this->defaultLimit,
            $this->positiveValuesOnly,
            $parameters['conditions'] ?? []
        ))->getCache();
    }
}
