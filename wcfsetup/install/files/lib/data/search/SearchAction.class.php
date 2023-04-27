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

/**
 * Executes search-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Search      create()
 * @method  SearchEditor[]  getObjects()
 * @method  SearchEditor    getSingleObject()
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

        if (!\in_array($this->parameters['sortField'], ['subject', 'time', 'username', 'relevance'])) {
            $this->parameters['sortField'] = SEARCH_DEFAULT_SORT_FIELD;
        }

        if (!\in_array($this->parameters['sortOrder'], ['ASC', 'DESC'])) {
            $this->parameters['sortOrder'] = SEARCH_DEFAULT_SORT_ORDER;
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
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.page.error.flood'));
        }
    }

    /**
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

        $startIndex = 0;
        if ($this->parameters['pageNo'] > 1) {
            $startIndex = SEARCH_RESULTS_PER_PAGE * ($this->parameters['pageNo'] - 1);
        }
        $resultHandler = new SearchResultHandler($search, $startIndex);
        $resultHandler->loadSearchResults();
        $templateName = $resultHandler->getTemplateName();
        SearchResultTextParser::getInstance()->setSearchQuery($resultHandler->getQuery());

        WCF::getTPL()->assign([
            'objects' => $resultHandler->getSearchResults(),
            'query' => $resultHandler->getQuery(),
            'customIcons' => $resultHandler->getCustomIcons(),
        ]);

        return [
            'count' => $resultHandler->countSearchResults(),
            'title' => WCF::getLanguage()->getDynamicVariable('wcf.search.results.title', [
                'count' => $resultHandler->countSearchResults(),
                'query' => $resultHandler->getQuery(),
            ]),
            'pages' => \ceil($resultHandler->countSearchResults() / SEARCH_RESULTS_PER_PAGE),
            'pageNo' => $this->parameters['pageNo'] ?: 1,
            'searchID' => $search->searchID,
            'template' => WCF::getTPL()->fetch($templateName['templateName'], $templateName['application']),
        ];
    }

    /**
     * @since 5.5
     */
    public function validateGetSearchResults(): void
    {
        $this->readInteger('searchID');
        $this->readInteger('pageNo');

        $search = new Search($this->parameters['searchID']);
        if (!$search->searchID || $search->searchType != 'messages') {
            throw new IllegalLinkException();
        }
        if ($search->userID && $search->userID != WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @since 5.5
     */
    public function getSearchResults(): array
    {
        $search = new Search($this->parameters['searchID']);
        $resultHandler = new SearchResultHandler($search, SEARCH_RESULTS_PER_PAGE * ($this->parameters['pageNo'] - 1));
        $resultHandler->loadSearchResults();
        $templateName = $resultHandler->getTemplateName();
        SearchResultTextParser::getInstance()->setSearchQuery($resultHandler->getQuery());

        WCF::getTPL()->assign([
            'objects' => $resultHandler->getSearchResults(),
            'query' => $resultHandler->getQuery(),
            'customIcons' => $resultHandler->getCustomIcons(),
        ]);

        return [
            'template' => WCF::getTPL()->fetch($templateName['templateName'], $templateName['application']),
        ];
    }
}
