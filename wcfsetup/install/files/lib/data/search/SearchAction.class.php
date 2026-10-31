<?php

namespace wcf\data\search;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\flood\FloodControl;
use wcf\system\search\SearchEngine;
use wcf\system\search\SearchHandler;
use wcf\system\search\SearchResultHandler;
use wcf\system\search\SearchResultTextParser;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Executes search-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Search, SearchEditor>
 */
class SearchAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = SearchEditor::class;

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getSearchResults', 'search'];

    /**
     * @var int
     */
    private const ALLOWED_REQUESTS_PER_24H = 600;

    /**
     * @var int
     */
    private const ALLOWED_REQUESTS_PER_60S = 20;

    /**
     * @since 5.5
     */
    public function validateSearch(): void
    {
        $this->readString('q');
        $this->readString('type', true);
        $this->readString('usernames', true);
        $this->readBoolean('subjectOnly', true);
        $this->readString('startDate', true);
        $this->readString('endDate', true);
        $this->readString('sortField', true);
        $this->readString('sortOrder', true);
        $this->readInteger('pageNo', true);

        if (!empty($this->parameters['type'])) {
            if (SearchEngine::getInstance()->getObjectType($this->parameters['type']) === null) {
                throw new IllegalLinkException();
            }
        }

        if (!\in_array($this->parameters['sortField'], ['subject', 'time', 'username', 'relevance'], true)) {
            $this->parameters['sortField'] = \SEARCH_DEFAULT_SORT_FIELD;
        }

        if (!\in_array($this->parameters['sortOrder'], ['ASC', 'DESC'], true)) {
            $this->parameters['sortOrder'] = \SEARCH_DEFAULT_SORT_ORDER;
        }

        $requestsPer24h = FloodControl::getInstance()->countContent(
            'com.woltlab.wcf.search',
            new \DateInterval('PT24H')
        );
        $requestsPer60s = FloodControl::getInstance()->countContent(
            'com.woltlab.wcf.search',
            new \DateInterval('PT60S')
        );
        if (
            $requestsPer24h['count'] >= self::ALLOWED_REQUESTS_PER_24H
            || $requestsPer60s['count'] >= self::ALLOWED_REQUESTS_PER_60S
        ) {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->getDynamicVariable('wcf.page.error.flood')
            ));
        }
    }

    /**
     * @return array{
     *  count: int,
     *  title: string,
     *  pageNo: int,
     *  template: string,
     *  containerCssClassName: string,
     * }|array{count: int, title: string}
     * @since 5.5
     */
    public function search(): array
    {
        $handler = new SearchHandler($this->parameters);
        $search = $handler->search();
        FloodControl::getInstance()->registerContent('com.woltlab.wcf.search');
        if ($search === null) {
            return [
                'count' => 0,
                'title' => WCF::getLanguage()->getDynamicVariable('wcf.search.results.title', [
                    'count' => 0,
                    'query' => $this->parameters['q'] ?? '',
                ]),
            ];
        }

        $resultHandler = new SearchResultHandler($search);
        $listView = $resultHandler->getResultListView();
        $listView->setPageNo(\max(1, $this->parameters['pageNo']));

        return [
            'count' => $listView->countItems(),
            'title' => WCF::getLanguage()->getDynamicVariable('wcf.search.results.title', [
                'count' => $listView->countItems(),
                'query' => $resultHandler->getQuery(),
            ]),
            'pageNo' => $listView->getPageNo(),
            'template' => $listView->render(),
            'containerCssClassName' => $listView->getContainerCssClassName(),
        ];
    }

    /**
     * @since 5.5
     * @deprecated 6.3 The search results are paginated by `SearchResultListView`.
     */
    public function validateGetSearchResults(): void
    {
        $this->readInteger('searchID');
        $this->readInteger('pageNo');

        $search = new Search($this->parameters['searchID']);
        if (!$search->canAccess()) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @return array{template: string}
     * @since 5.5
     * @deprecated 6.3 The search results are paginated by `SearchResultListView`.
     */
    public function getSearchResults(): array
    {
        $search = new Search($this->parameters['searchID']);
        $resultHandler = new SearchResultHandler($search, \SEARCH_RESULTS_PER_PAGE * ($this->parameters['pageNo'] - 1));
        $resultHandler->loadSearchResults();
        $templateName = $resultHandler->getTemplateName();
        SearchResultTextParser::getInstance()->setSearchQuery($resultHandler->getQuery());

        return [
            'template' => WCF::getTPL()->render($templateName['application'], $templateName['templateName'], [
                'objects' => $resultHandler->getSearchResults(),
                'query' => $resultHandler->getQuery(),
                'customIcons' => $resultHandler->getCustomIcons(),
            ]),
        ];
    }
}
