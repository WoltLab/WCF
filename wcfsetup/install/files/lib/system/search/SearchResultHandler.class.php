<?php

namespace wcf\system\search;

use wcf\data\search\ISearchResultObject;
use wcf\data\search\Search;
use wcf\page\SearchResultPage;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;

/**
 * Provides the results of a full-text search.
 *
 * @author  Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */
final class SearchResultHandler
{
    private readonly Search $search;

    /**
     * @var array{
     *  query: string,
     *  objectTypeNames: string[],
     *  results: list<array{objectID: int, objectType: string}>,
     *  additionalData: array<string, mixed>
     * }
     */
    private array $searchData;

    /**
     * @var list<ISearchResultObject>
     */
    private array $messages = [];

    private int $startIndex = 0;

    private int $limit = 0;

    private int $endIndex = 0;

    public function __construct(Search $search, int $startIndex = 0, int $limit = \SEARCH_RESULTS_PER_PAGE)
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

    /**
     * @return list<ISearchResultObject>
     */
    public function getSearchResults(): array
    {
        return $this->messages;
    }

    private function cacheMessageData(): void
    {
        $types = [];
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
            $message = $objectType->getObject($objectID);
            if ($message !== null) {
                // @phpstan-ignore instanceof.alwaysTrue
                if (!($message instanceof ISearchResultObject)) {
                    throw new ImplementationException(\get_class($message), ISearchResultObject::class);
                }

                $this->messages[] = $message;
            }
        }
    }

    public function getQuery(): string
    {
        return $this->searchData['query'];
    }

    /**
     * @return array{templateName: string, application: string}
     */
    public function getTemplateName(): array
    {
        if (\count($this->searchData['objectTypeNames']) === 1) {
            $objectType = SearchEngine::getInstance()->getObjectType($this->searchData['objectTypeNames'][0]);
            if ($objectType instanceof ISearchProvider) {
                if (($templateName = $objectType->getResultListTemplateName()) !== '') {
                    return [
                        'templateName' => $templateName,
                        'application' => $objectType->getApplication(),
                    ];
                }
            }
        }

        return $this->getLegacyTemplateName();
    }

    /**
     * @return array<string, string|null>
     */
    public function getCustomIcons(): array
    {
        $customIcons = [];
        foreach (SearchEngine::getInstance()->getAvailableObjectTypes() as $name => $type) {
            if ($type instanceof ISearchProvider) {
                $customIcons[$name] = $type->getCustomIconName();
            }
        }

        return $customIcons;
    }

    /**
     * Will be removed with 6.0 once all search providers have switched to ISearchProvider.
     *
     * @return array{templateName: string, application: string}
     * @deprecated 5.5
     */
    private function getLegacyTemplateName(): array
    {
        $page = new SearchResultPage();
        $page->assignVariables();

        return [
            'templateName' => $page->resultListTemplateName,
            'application' => $page->resultListApplication,
        ];
    }
}
