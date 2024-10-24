<?php

namespace wcf\data\search\keyword;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Executes keyword-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  SearchKeyword       create()
 * @method  SearchKeywordEditor[]   getObjects()
 * @method  SearchKeywordEditor getSingleObject()
 */
class SearchKeywordAction extends AbstractDatabaseObjectAction implements ISearchAction
{
    /**
     * @inheritDoc
     */
    protected $className = SearchKeywordEditor::class;

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getSearchResultList'];

    /**
     * @inheritDoc
     */
    public function validateGetSearchResultList()
    {
        if (FORCE_LOGIN && !WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        $this->readString('searchString', false, 'data');
    }

    /**
     * @inheritDoc
     */
    public function getSearchResultList()
    {
        $list = [];

        // find users
        $sql = "SELECT      *
                FROM        wcf1_search_keyword
                WHERE       keyword LIKE ?
                ORDER BY    searches DESC";
        $statement = WCF::getDB()->prepare($sql, 10);
        $statement->execute([$this->parameters['data']['searchString'] . '%']);
        while ($row = $statement->fetchArray()) {
            $list[] = [
                'label' => $row['keyword'],
                'objectID' => $row['keywordID'],
            ];
        }

        return $list;
    }

    /**
     * Inserts a new keyword if it does not already exist, or updates it if it does.
     * @since 5.2
     */
    public function registerSearch()
    {
        $sql = "INSERT INTO             wcf1_search_keyword
                                        (keyword, searches, lastSearchTime)
                VALUES                  (?, ?, ?)
                ON DUPLICATE KEY UPDATE searches = searches + VALUES(searches),
                                        lastSearchTime = VALUES(lastSearchTime)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            \mb_substr($this->parameters['data']['keyword'], 0, 191),
            ($this->parameters['data']['searches'] ?? 1),
            ($this->parameters['data']['lastSearchTime'] ?? TIME_NOW),
        ]);
    }
}
