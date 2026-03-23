<?php

namespace wcf\system\search;

/**
 * Default interface for search index managers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ISearchIndexManager
{
    /**
     * Adds or updates an entry.
     *
     * @return void
     */
    public function set(
        string $objectType,
        int $objectID,
        string $message,
        string $subject,
        int $time,
        ?int $userID,
        string $username,
        ?int $languageID = null,
        string $metaData = ''
    );

    /**
     * Deletes search index entries.
     *
     * @param int[] $objectIDs
     * @return void
     */
    public function delete(string $objectType, array $objectIDs);

    /**
     * Resets the search index.
     *
     * @return void
     */
    public function reset(string $objectType);

    /**
     * Creates the search index for all searchable objects.
     *
     * @return void
     */
    public function createSearchIndices();

    /**
     * Begins the bulk operation.
     *
     * @return void
     */
    public function beginBulkOperation();

    /**
     * Commits the bulk operation.
     *
     * @return void
     */
    public function commitBulkOperation();
}
