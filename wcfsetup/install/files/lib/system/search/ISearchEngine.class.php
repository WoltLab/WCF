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
     * @param string $objectTypeName
     * @param string $q
     * @param bool $subjectOnly
     * @param PreparedStatementConditionBuilder $searchIndexCondition
     * @param string $orderBy
     * @param int $limit
     * @return  array{
     *              fulltextCondition: ?PreparedStatementConditionBuilder
     *              searchIndexCondition: ?PreparedStatementConditionBuilder
     *              sql: string
     *          }
     */
    public function getInnerJoin(
        $objectTypeName,
        $q,
        $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        $orderBy = 'time DESC',
        $limit = 1000
    );

    /**
     * Removes engine-specific special characters from a string.
     *
     * @param string $string
     */
    public function removeSpecialCharacters($string);

    /**
     * Searches for the given string and returns the data of the found messages.
     *
     * @param string $q
     * @param array $objectTypes
     * @param bool $subjectOnly
     * @param PreparedStatementConditionBuilder $searchIndexCondition
     * @param array $additionalConditions
     * @param string $orderBy
     * @param int $limit
     * @return  array
     */
    public function search(
        $q,
        array $objectTypes,
        $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        array $additionalConditions = [],
        $orderBy = 'time DESC',
        $limit = 1000
    );
}
