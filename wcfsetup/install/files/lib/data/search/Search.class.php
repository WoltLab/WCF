<?php

namespace wcf\data\search;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a search.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $searchID       unique id of the search
 * @property-read   ?int    $userID         id of the user who has done the search or `null` if a guest has done the search
 * @property-read   string  $searchData     serialized array with data and parameters of the seatch
 * @property-read   int     $searchTime     timestamp of the search
 * @property-read   string  $searchType     type of search, like `messages` or `users`
 * @property-read   string  $searchHash     hash identifying the search for the user to reuse the result within the first 30 minutes
 *
 * @phpstan-type SearchData array{
 *  results: list<array{objectID: int, objectType: string}>,
 *  query: string,
 *  additionalData: array<string, mixed>,
 *  sortField: string,
 *  sortOrder: string,
 *  objectTypeNames: string[],
 * }
 */
class Search extends DatabaseObject
{
    /**
     * @var SearchData
     */
    private array $unserializedSearchData;

    /**
     * Returns true if the active user can access the results of this message search.
     *
     * @since 6.3
     */
    public function canAccess(): bool
    {
        if ($this->isNil() || $this->searchType !== 'messages') {
            return false;
        }

        return $this->userID === null || $this->userID === WCF::getUser()->userID;
    }

    /**
     * Returns the unserialized data and parameters of this search.
     *
     * @return SearchData
     * @since 6.3
     */
    public function getSearchData(): array
    {
        if (!isset($this->unserializedSearchData)) {
            $this->unserializedSearchData = \unserialize($this->searchData);
        }

        return $this->unserializedSearchData;
    }
}
