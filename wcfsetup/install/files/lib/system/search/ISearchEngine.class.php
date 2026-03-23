<?php

namespace wcf\system\search;

use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Default interface for search engines.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ISearchEngine
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
     *              fulltextCondition: ?PreparedStatementConditionBuilder,
     *              searchIndexCondition: ?PreparedStatementConditionBuilder,
     *              sql: string,
     *          }
     */
    public function getInnerJoin(
        string $objectTypeName,
        string $q,
        bool $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        string $orderBy = 'time DESC',
        int $limit = 1000
    );

    /**
     * Removes engine-specific special characters from a string.
     *
     * @return string
     */
    public function removeSpecialCharacters(string $string);

    /**
     * Searches for the given string and returns the data of the found messages.
     *
     * @param string[] $objectTypes
     * @param array<string, PreparedStatementConditionBuilder> $additionalConditions
     * @return list<array{objectID: int, objectType: string}>
     */
    public function search(
        string $q,
        array $objectTypes,
        bool $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        array $additionalConditions = [],
        string $orderBy = 'time DESC',
        int $limit = 1000
    );
}
