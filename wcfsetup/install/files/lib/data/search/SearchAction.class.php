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

    public function validateSearch(): void
    {
        $this->readString('q', true);
        $this->readString('type', true);
        $this->readString('username', true);
        $this->readBoolean('nameExactly', true);
        $this->readBoolean('subjectOnly', true);
        $this->readString('startDate', true);
        $this->readString('endDate', true);

        if (empty($this->parameters['q']) && empty($this->parameters['username'])) {
            throw new UserInputException('q');
        }

        if (!empty($this->parameters['type'])) {
            if (SearchEngine::getInstance()->getObjectType($this->parameters['type']) === null) {
                throw new IllegalLinkException();
            }
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
            'searchID' => $search->searchID,
            'template' => WCF::getTPL()->fetch('searchResultList'),
        ];
    }
}
