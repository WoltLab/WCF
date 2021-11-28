<?php

namespace wcf\system\search;

use wcf\data\search\keyword\SearchKeywordAction;
use wcf\data\search\Search;
use wcf\data\search\SearchAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

final class SearchHandler
{
    /**
     * @var mixed[]
     */
    private $parameters = [];

    /**
     * @var string[]
     */
    private $objectTypeNames = [];

    /**
     * @var PreparedStatementConditionBuilder
     */
    private $conditionBuilder;

    /**
     * @var PreparedStatementConditionBuilder[]
     */
    private $typeBasedConditionBuilders = [];

    /**
     * @var int[]
     */
    private $userIDs;

    /**
     * @var string
     */
    private $searchHash = '';

    /**
     * @var mixed[]
     */
    private $results = [];

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function search()
    {
        $this->initParameters();
        $this->buildConditions();
        $this->buildSearchHash();

        if (($search = $this->getExistingSearch()) !== null) {
            return $search;
        }

        if (!$this->loadResults()) {
            return null;
        }

        $this->saveKeywordSuggestion();
        return $this->saveSearch();
    }

    private function initParameters(): void
    {
        if (empty($this->parameters['sortField'])) {
            $this->parameters['sortField'] = SEARCH_DEFAULT_SORT_FIELD;
        }
        if (empty($this->parameters['sortOrder'])) {
            $this->parameters['sortOrder'] = SEARCH_DEFAULT_SORT_ORDER;
        }
    }

    private function buildConditions(): void
    {
        $this->initObjectTypeNames();
        $this->initConditionBuilder();

        $this->buildUserCondition();
        $this->buildDateCondition();
        $this->buildLanguageCondition();
        $this->buildTypeBasedConditionBuilders();
    }

    private function initObjectTypeNames(): void
    {
        if (!empty($this->parameters['type'])) {
            $this->objectTypeNames[] =  $this->parameters['type'];
        } else {
            $this->objectTypeNames = \array_keys(SearchEngine::getInstance()->getAvailableObjectTypes());
        }
    }

    private function initConditionBuilder(): void
    {
        $conditionBuilderClassName = SearchEngine::getInstance()->getConditionBuilderClassName();
        $this->conditionBuilder = new $conditionBuilderClassName(false);
    }

    private function buildUserCondition(): void
    {
        $userIDs = $this->getUserIDs();
        if (!empty($userIDs)) {
            $this->conditionBuilder->add('userID IN (?)', [$userIDs]);
        }
    }

    private function buildDateCondition(): void
    {
        $startDate = !empty($this->parameters['startDate']) ? @\strtotime($this->parameters['startDate']) : 0;
        $endDate = !empty($this->parameters['endDate']) ? @\strtotime($this->parameters['endDate']) : 0;
        if ($startDate && $endDate) {
            $this->conditionBuilder->add('time BETWEEN ? AND ?', [$startDate, $endDate]);
        } elseif ($startDate) {
            $this->conditionBuilder->add('time > ?', [$startDate]);
        } elseif ($endDate) {
            $this->conditionBuilder->add('time < ?', [$endDate]);
        }
    }

    private function buildLanguageCondition(): void
    {
        if (
            !empty($this->parameters['q'])
            && LanguageFactory::getInstance()->multilingualismEnabled()
            && \count(WCF::getUser()->getLanguageIDs())
        ) {
            $this->conditionBuilder->add(
                '(languageID IN (?) OR languageID = 0)',
                [WCF::getUser()->getLanguageIDs()]
            );
        }
    }

    private function buildTypeBasedConditionBuilders(): void
    {
        foreach ($this->objectTypeNames as $key => $objectTypeName) {
            $objectType = SearchEngine::getInstance()->getObjectType($objectTypeName);
            if ($objectType === null) {
                throw new SystemException('unknown search object type ' . $objectTypeName);
            }

            try {
                if (!$objectType->isAccessible()) {
                    throw new PermissionDeniedException();
                }

                if (($conditionBuilder = $objectType->getConditions()) !== null) {
                    $this->typeBasedConditionBuilders[$objectTypeName] = $conditionBuilder;
                }
            } catch (PermissionDeniedException $e) {
                unset($this->objectTypeNames[$key]);
                continue;
            }
        }
    }

    private function getUserIDs(): array
    {
        if ($this->userIDs === null) {
            $this->userIDs = [];

            if (!empty($this->parameters['username'])) {
                $sql = "SELECT  userID
                        FROM    wcf" . WCF_N . "_user
                        WHERE   username " . (!empty($this->parameters['nameExactly']) ? "= ?" : "LIKE ?");
                $statement = WCF::getDB()->prepareStatement($sql, 100);
                $statement->execute([$this->username . (empty($this->parameters['nameExactly']) ? '%' : '')]);
                $this->userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
            }

            if (!empty($this->parameters['userID'])) {
                $this->userIDs[] = $this->parameters['userID'];
            }
        }

        return $this->userIDs;
    }

    private function saveKeywordSuggestion(): void
    {
        if (!empty($this->parameters['q'])) {
            (new SearchKeywordAction([], 'registerSearch', [
                'data' => [
                    'keyword' => $this->parameters['q'],
                ],
            ]))->executeAction();
        }
    }

    private function buildSearchHash(): void
    {
        $this->searchHash = \sha1(\serialize([
            $this->parameters['q'] ?? '',
            $this->objectTypeNames,
            $this->parameters['subjectOnly'] ?? 0,
            $this->conditionBuilder,
            $this->typeBasedConditionBuilders,
            $this->parameters['sortField'],
            $this->parameters['sortOrder'],
        ]));
    }

    private function getExistingSearch(): ?Search
    {
        if (!empty($this->parameters['q'])) {
            $parameters = [$this->searchHash, 'messages', TIME_NOW - 1800];
            if (WCF::getUser()->userID) {
                $parameters[] = WCF::getUser()->userID;
            }

            $sql = "SELECT  searchID
                    FROM    wcf" . WCF_N . "_search
                    WHERE   searchHash = ?
                        AND searchType = ?
                        AND searchTime > ?
                        " . (WCF::getUser()->userID ? 'AND userID = ?' : 'AND userID IS NULL');
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($parameters);
            if ($searchID = $statement->fetchSingleColumn()) {
                return new Search($searchID);
            }
        }

        return null;
    }

    private function loadResults(): bool
    {
        $this->results = SearchEngine::getInstance()->search(
            $this->parameters['q'] ?? '',
            $this->objectTypeNames,
            $this->parameters['subjectOnly'] ?? 0,
            $this->conditionBuilder,
            $this->typeBasedConditionBuilders,
            $this->parameters['sortField'] . ' ' . $this->parameters['sortOrder']
        );

        return (!empty($this->results));
    }

    private function saveSearch(): Search
    {
        $additionalData = [];
        foreach ($this->objectTypeNames as $objectTypeName) {
            $objectType = SearchEngine::getInstance()->getObjectType($objectTypeName);
            if (($data = $objectType->getAdditionalData()) !== null) {
                $additionalData[$objectTypeName] = $data;
            }
        }

        $this->searchData = [
            'results' => $this->results,
            'query' => $this->parameters['q'] ?? '',
            'additionalData' => $additionalData,
            'sortField' => $this->parameters['sortField'] ?? '',
            'sortOrder' => $this->parameters['sortOrder'] ?? '',
            'nameExactly' => $this->parameters['nameExactly'] ?? '',
            'subjectOnly' => $this->parameters['subjectOnly'] ?? '',
            'startDate' => $this->parameters['startDate'] ?? '',
            'endDate' => $this->parameters['endDate'] ?? '',
            'username' =>  $this->parameters['username'] ?? '',
            'userID' => $this->parameters['userID'] ?? '',
            'objectTypeNames' => $this->objectTypeNames,
        ];

        $this->objectAction = new SearchAction([], 'create', [
            'data' => [
                'userID' => WCF::getUser()->userID ?: null,
                'searchData' => \serialize($this->searchData),
                'searchTime' => TIME_NOW,
                'searchType' => 'messages',
                'searchHash' => $this->searchHash,
            ],
        ]);
        $resultValues = $this->objectAction->executeAction();
        return $resultValues['returnValues'];
    }
}
