<?php

namespace wcf\data\search;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\search\SearchEngine;
use wcf\system\search\SearchHandler;
use wcf\system\search\SearchResultHandler;
use wcf\system\WCF;

/**
 * Executes search-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Search
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
    protected $allowGuestAccess = ['search'];

    public function validateSearch(): void
    {
        $this->readString('q', true);
        $this->readString('type', true);
        $this->readString('username', true);
        $this->readBoolean('nameExactly', true);
        $this->readBoolean('subjectOnly', true);
        $this->readString('startDate', true);
        $this->readString('endDate', true);
        $this->readString('sortField', true);
        $this->readString('sortOrder', true);

        if (empty($this->parameters['q']) && empty($this->parameters['username'])) {
            throw new UserInputException('q');
        }

        if (!empty($this->parameters['type'])) {
            if (SearchEngine::getInstance()->getObjectType($this->parameters['type']) === null) {
                throw new IllegalLinkException();
            }
        }

        if (\in_array($this->parameters['sortField'], ['subject', 'time', 'username', 'relevance'])) {
            $this->parameters['sortField'] = SEARCH_DEFAULT_SORT_FIELD;
        }

        if ($this->parameters['sortOrder'] !== 'ASC' && $this->parameters['sortOrder'] !== 'DESC') {
            $this->parameters['sortOrder'] = SEARCH_DEFAULT_SORT_ORDER;
        }
    }

    public function search(): array
    {
        $handler = new SearchHandler($this->parameters);
        $search = $handler->search();
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
        $resultHandler->loadSearchResults();
        $templateName = $resultHandler->getTemplateName();

        WCF::getTPL()->assign([
            'objects' => $resultHandler->getSearchResults(),
            'customIcons' => $resultHandler->getCustomIcons(),
            'query' => $resultHandler->getQuery(),
        ]);

        return [
            'count' => $resultHandler->countSearchResults(),
            'title' => WCF::getLanguage()->getDynamicVariable('wcf.search.results.title', [
                'count' => $resultHandler->countSearchResults(),
                'query' => $resultHandler->getQuery(),
            ]),
            'pages' => \ceil($resultHandler->countSearchResults() / SEARCH_RESULTS_PER_PAGE),
            'searchID' => $search->searchID,
            'template' => WCF::getTPL()->fetch($templateName['templateName'], $templateName['application']),
        ];
    }

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

    public function getSearchResults(): array
    {
        $search = new Search($this->parameters['searchID']);
        $resultHandler = new SearchResultHandler($search, SEARCH_RESULTS_PER_PAGE * ($this->parameters['pageNo'] - 1));
        $resultHandler->loadSearchResults();
        $templateName = $resultHandler->getTemplateName();

        WCF::getTPL()->assign([
            'objects' => $resultHandler->getSearchResults(),
            'customIcons' => $resultHandler->getCustomIcons(),
            'query' => $resultHandler->getQuery(),
        ]);

        return [
            'template' => WCF::getTPL()->fetch($templateName['templateName'], $templateName['application']),
        ];
    }
}
