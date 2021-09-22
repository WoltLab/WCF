<?php

namespace wcf\system\search;

/**
 * Default interface for search index managers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Search
 */
interface ISearchIndexManager
{
    /**
     * Adds or updates an entry.
     *
     * @param string $objectType
     * @param int $objectID
     * @param string $message
     * @param string $subject
     * @param int $time
     * @param int $userID
     * @param string $username
     * @param int $languageID
     * @param string $metaData
     */
    public function set(
        $objectType,
        $objectID,
        $message,
        $subject,
        $time,
        $userID,
        $username,
        $languageID = null,
        $metaData = ''
    );

    /**
     * Deletes search index entries.
     *
     * @param string $objectType
     * @param int[] $objectIDs
     */
    public function delete($objectType, array $objectIDs);

    /**
     * Resets the search index.
     *
     * @param string $objectType
     */
    public function reset($objectType);

    /**
     * Creates the search index for all searchable objects.
     */
    public function createSearchIndices();

    /**
     * Begins the bulk operation.
     */
    public function beginBulkOperation();

    /**
     * Commits the bulk operation.
     */
    public function commitBulkOperation();
}
