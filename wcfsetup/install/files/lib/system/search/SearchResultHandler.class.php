<?php

namespace wcf\system\search;

use wcf\data\search\ICustomIconSearchResultObject;
use wcf\data\search\ISearchResultObject;
use wcf\data\search\Search;
use wcf\system\exception\ImplementationException;
use wcf\system\search\SearchEngine;

final class SearchResultHandler
{
    /**
     * @var Search
     */
    private $search;

    /**
     * @var array
     */
    private $searchData;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var string[]
     */
    private $customIcons = [];

    /**
     * @var int
     */
    private $startIndex = 0;

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * @var int
     */
    private $endIndex = 0;

    public function __construct(Search $search, int $startIndex = 0, int $limit = SEARCH_RESULTS_PER_PAGE)
    {
        $this->search = $search;
        $this->startIndex = $startIndex;
        $this->limit = $limit;
        $this->searchData = \unserialize($this->search->searchData);
    }

    public function countSearchResults(): int
    {
        return \count($this->searchData['results']);
    }

    public function loadSearchResults(): void
    {
        if ($this->startIndex >= $this->countSearchResults()) {
            $this->startIndex = $this->countSearchResults() - 1;
        }
        $this->endIndex = $this->startIndex + $this->limit;
        if ($this->endIndex > $this->countSearchResults()) {
            $this->endIndex = $this->countSearchResults();
        }

        $this->cacheMessageData();
        $this->readMessages();
    }

    public function getSearchResults(): array
    {
        return $this->messages;
    }

    public function getCustomIcons(): array
    {
        return $this->customIcons;
    }

    private function cacheMessageData(): void
    {
        $types = [];

        // group object id by object type
        for ($i = $this->startIndex; $i < $this->endIndex; $i++) {
            $type = $this->searchData['results'][$i]['objectType'];
            $objectID = $this->searchData['results'][$i]['objectID'];

            if (!isset($types[$type])) {
                $types[$type] = [];
            }
            $types[$type][] = $objectID;
        }

        foreach ($types as $type => $objectIDs) {
            $objectType = SearchEngine::getInstance()->getObjectType($type);
            $objectType->cacheObjects($objectIDs, ($this->searchData['additionalData'][$type] ?? null));
        }
    }

    private function readMessages(): void
    {
        for ($i = $this->startIndex; $i < $this->endIndex; $i++) {
            $type = $this->searchData['results'][$i]['objectType'];
            $objectID = $this->searchData['results'][$i]['objectID'];

            $objectType = SearchEngine::getInstance()->getObjectType($type);
            if (($message = $objectType->getObject($objectID)) !== null) {
                if (!($message instanceof ISearchResultObject)) {
                    throw new ImplementationException(\get_class($message), ISearchResultObject::class);
                }

                $customIcon = '';
                if ($message instanceof ICustomIconSearchResultObject) {
                    $customIcon = $message->getCustomSearchResultIcon();
                }

                $this->messages[] = $message;
                $this->customIcons[\spl_object_hash($message)] = $customIcon;
            }
        }
    }

    public function getQuery(): string
    {
        return $this->searchData['query'];
    }
}
