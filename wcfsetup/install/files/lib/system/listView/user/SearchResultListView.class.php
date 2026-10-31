<?php

namespace wcf\system\listView\user;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;
use wcf\data\search\ISearchResultObject;
use wcf\data\search\Search;
use wcf\event\listView\user\SearchResultListViewInitialized;
use wcf\system\exception\ImplementationException;
use wcf\system\listView\AbstractListView;
use wcf\system\search\SearchResultHandler;
use wcf\system\search\SearchResultTextParser;
use wcf\system\WCF;

/**
 * List view that shows the results of a full-text search.
 *
 * The results are not backed by a database object list, but by the ordered list of
 * object ids that has been stored for the search. Sorting and filtering happen
 * when the search is executed, therefore both are disabled for this list view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractListView<(DatabaseObject&ISearchResultObject)|(DatabaseObjectDecorator&ISearchResultObject), DatabaseObjectList<DatabaseObject>>
 */
final class SearchResultListView extends AbstractListView
{
    private readonly Search $search;
    private SearchResultHandler $resultHandler;

    public function __construct(
        public readonly int $searchID
    ) {
        $this->search = new Search($this->searchID);

        $this->setItemsPerPage(\SEARCH_RESULTS_PER_PAGE);
        $this->setAllowFiltering(false);
        $this->setAllowSorting(false);
        $this->setAllowInteractions(false);
        $this->setAllowBulkInteractions(false);
    }

    #[\Override]
    public function isAccessible(): bool
    {
        if ($this->search->isNil() || $this->search->searchType !== 'messages') {
            return false;
        }

        return $this->search->userID === null || $this->search->userID === WCF::getUser()->userID;
    }

    #[\Override]
    protected function initObjectList(): void
    {
        $this->objectList = $this->createObjectList();
        $this->fireInitializedEvent();

        $this->objectCount = new SearchResultHandler($this->search)->countSearchResults();
        if ($this->getPageNo() > 1 && $this->getPageNo() > $this->countPages()) {
            $this->setPageNo($this->countPages() ?: 1);
        }

        $this->resultHandler = new SearchResultHandler(
            $this->search,
            ($this->getPageNo() - 1) * $this->getItemsPerPage(),
            $this->getItemsPerPage()
        );
        $this->resultHandler->loadSearchResults();
    }

    #[\Override]
    protected function applyFilters(): void
    {
        // Filters are applied by the search engine when the search is executed.
    }

    /**
     * @return list<(DatabaseObject&ISearchResultObject)|(DatabaseObjectDecorator&ISearchResultObject)>
     */
    #[\Override]
    public function getItems(): array
    {
        $items = [];
        foreach ($this->getResultHandler()->getSearchResults() as $item) {
            if (!($item instanceof DatabaseObject) && !($item instanceof DatabaseObjectDecorator)) {
                throw new ImplementationException(\get_class($item), DatabaseObjectDecorator::class);
            }

            $items[] = $item;
        }

        return $items;
    }

    #[\Override]
    public function countItems(): int
    {
        $this->getObjectList();

        return $this->objectCount;
    }

    public function getQuery(): string
    {
        return $this->getResultHandler()->getQuery();
    }

    #[\Override]
    public function renderItems(): string
    {
        $resultHandler = $this->getResultHandler();
        $templateName = $resultHandler->getTemplateName();
        SearchResultTextParser::getInstance()->setSearchQuery($resultHandler->getQuery());

        return WCF::getTPL()->render($templateName['application'], $templateName['templateName'], [
            'objects' => $resultHandler->getSearchResults(),
            'query' => $resultHandler->getQuery(),
            'customIcons' => $resultHandler->getCustomIcons(),
            'view' => $this,
        ]);
    }

    private function getResultHandler(): SearchResultHandler
    {
        $this->getObjectList();

        return $this->resultHandler;
    }

    #[\Override]
    protected function getInitializedEvent(): SearchResultListViewInitialized
    {
        return new SearchResultListViewInitialized($this);
    }

    /**
     * @return DatabaseObjectList<DatabaseObject>
     */
    #[\Override]
    protected function createObjectList(): DatabaseObjectList
    {
        return new class extends DatabaseObjectList {};
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'searchID' => $this->searchID,
        ];
    }
}
