<?php

namespace wcf\system\search;

use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Default interface for search engines that support
 * filtering by context.
 *
 * CAUTION: This is an experimental API that is not designed
 *          for general consumption.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
interface IContextAwareSearchEngine extends ISearchEngine
{
    /**
     * Returns the condition builder class name required to provide conditions for getInnerJoin().
     *
     * @return  string
     */
    public function getConditionBuilderClassName();

    /**
     * Returns the inner join query and the condition parameters. This method is allowed to return NULL for both the
     * 'fulltextCondition' and 'searchIndexCondition' index instead of a PreparedStatementConditionBuilder instance.
     *
     * @return  array{
     *              fulltextCondition: ?PreparedStatementConditionBuilder
     *              searchIndexCondition: ?PreparedStatementConditionBuilder
     *              sql: string
     *          }
     */
    public function getInnerJoinWithContext(
        string $objectTypeName,
        string $q,
        bool $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        array $contextFilter = [],
        string $orderBy = 'time DESC',
        int $limit = 1000
    ): array;

    /**
     * Searches for the given string and returns the data of the found messages.
     */
    public function searchWithContext(
        string $q,
        array $objectTypes,
        bool $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        array $contextFilter = [],
        array $additionalConditions = [],
        string $orderBy = 'time DESC',
        int $limit = 1000
    ): array;
}
