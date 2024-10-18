<?php

namespace wcf\system\search;

use wcf\data\search\keyword\SearchKeywordAction;
use wcf\data\search\Search;
use wcf\data\search\SearchAction;
use wcf\data\user\UserList;
use wcf\form\SearchForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Performs full-text search.
 *
 * @author  Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */
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
     * @var mixed[]
     */
    private $typeBasedContextFilter = [];

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

        // Check if at least one author exists when searching for author.
        if (!empty($this->parameters['usernames']) && empty($this->getUserIDs())) {
            return null;
        }

        // Check if at least one object type is selected.
        // It may happen that a user does not have permission to use any object type.
        if ($this->objectTypeNames === []) {
            return null;
        }

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
        if (
            !empty($this->parameters['type'])
            && SearchEngine::getInstance()->getObjectType($this->parameters['type']) !== null
        ) {
            $this->objectTypeNames[] = $this->parameters['type'];
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
        $startDate = 0;
        if (!empty($this->parameters['startDate'])) {
            $startDateTime = \DateTime::createFromFormat(
                "Y-m-d",
                $this->parameters['startDate'],
                WCF::getUser()->getTimezone()
            );
            if ($startDateTime !== false) {
                $startDateTime->setTime(0, 0, 0);
                $startDate = $startDateTime->getTimestamp();
            }
        }

        $endDate = 0;
        if (!empty($this->parameters['endDate'])) {
            $endDateTime = \DateTime::createFromFormat(
                "Y-m-d",
                $this->parameters['endDate'],
                WCF::getUser()->getTimezone()
            );
            if ($endDateTime !== false) {
                $endDateTime->setTime(23, 59, 59);
                $endDate = $endDateTime->getTimestamp();
            }
        }

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
        $form = $this->getSearchFormEmulation();

        foreach ($this->objectTypeNames as $key => $objectTypeName) {
            $objectType = SearchEngine::getInstance()->getObjectType($objectTypeName);

            try {
                if (!$objectType->isAccessible()) {
                    throw new PermissionDeniedException();
                }

                if ($objectType instanceof ISearchProvider) {
                    $parameters = \count($this->objectTypeNames) === 1 ? $this->parameters : [];
                    if (($conditionBuilder = $objectType->getConditionBuilder($parameters)) !== null) {
                        $this->typeBasedConditionBuilders[$objectTypeName] = $conditionBuilder;
                    }

                    if (
                        \count($this->objectTypeNames) === 1
                        && ($newSortField = $objectType->getCustomSortField($this->parameters['sortField']))
                    ) {
                        $this->parameters['sortField'] = $newSortField;
                    }

                    if ($objectType instanceof IContextAwareSearchProvider) {
                        $this->typeBasedContextFilter[$objectTypeName] = $objectType->getContextFilter($parameters);
                    }
                } else {
                    if (($conditionBuilder = $objectType->getConditions($form)) !== null) {
                        $this->typeBasedConditionBuilders[$objectTypeName] = $conditionBuilder;
                    }
                }
            } catch (PermissionDeniedException $e) {
                unset($this->objectTypeNames[$key]);
                continue;
            }
        }

        // Make sure we have a sequential numerical index in the array.
        $this->objectTypeNames = \array_values($this->objectTypeNames);
    }

    /**
     * Will be removed with 6.0 once all search providers have switched to ISearchProvider.
     * @deprecated 5.5
     */
    private function getSearchFormEmulation(): SearchForm
    {
        foreach ($this->parameters as $key => $value) {
            $_POST[$key] = $value;
        }

        $form = new SearchForm();
        $form->readFormParameters();
        $form->userIDs = $this->getUserIDs();
        if (\count($form->selectedObjectTypes) === 1) {
            $this->objectTypeNames = $form->selectedObjectTypes;
        }
        if ($form->sortField) {
            $this->parameters['sortField'] = $form->sortField;
        }
        if ($form->sortOrder) {
            $this->parameters['sortOrder'] = $form->sortOrder;
        }

        return $form;
    }

    private function getUserIDs(): array
    {
        if ($this->userIDs === null) {
            $this->userIDs = [];

            if (!empty($this->parameters['usernames'])) {
                $userList = new UserList();
                $userList->getConditionBuilder()->add('username IN (?)', [ArrayUtil::trim(\explode(',', $this->parameters['usernames']))]);
                $userList->readObjectIDs();
                $this->userIDs = $userList->getObjectIDs();
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
            $this->typeBasedContextFilter,
            $this->parameters['sortField'],
            $this->parameters['sortOrder'],
            $this->getAdditionalData(),
        ]));
    }

    private function getExistingSearch(): ?Search
    {
        if (!empty($this->parameters['q'])) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('searchHash = ?', [$this->searchHash]);
            $conditionBuilder->add('searchType = ?', ['messages']);
            $conditionBuilder->add('searchTime > ?', [TIME_NOW - 1800]);
            if (WCF::getUser()->userID) {
                $conditionBuilder->add('userID = ?', [WCF::getUser()->userID]);
            } else {
                $conditionBuilder->add('userID IS NULL');
            }

            $sql = "SELECT  searchID
                    FROM    wcf1_search
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());
            if ($searchID = $statement->fetchSingleColumn()) {
                return new Search($searchID);
            }
        }

        return null;
    }

    private function loadResults(): bool
    {
        $this->results = SearchEngine::getInstance()->searchWithContext(
            $this->parameters['q'] ?? '',
            $this->objectTypeNames,
            $this->parameters['subjectOnly'] ?? 0,
            $this->conditionBuilder,
            $this->typeBasedContextFilter,
            $this->typeBasedConditionBuilders,
            $this->parameters['sortField'] . ' ' . $this->parameters['sortOrder']
        );

        return !empty($this->results);
    }

    private function saveSearch(): Search
    {
        $searchData = [
            'results' => $this->results,
            'query' => $this->parameters['q'] ?? '',
            'additionalData' => $this->getAdditionalData(),
            'sortField' => $this->parameters['sortField'] ?? '',
            'sortOrder' => $this->parameters['sortOrder'] ?? '',
            'subjectOnly' => $this->parameters['subjectOnly'] ?? '',
            'startDate' => $this->parameters['startDate'] ?? '',
            'endDate' => $this->parameters['endDate'] ?? '',
            'usernames' => $this->parameters['usernames'] ?? '',
            'userID' => $this->parameters['userID'] ?? '',
            'objectTypeNames' => $this->objectTypeNames,
        ];

        $objectAction = new SearchAction([], 'create', [
            'data' => [
                'userID' => WCF::getUser()->userID ?: null,
                'searchData' => \serialize($searchData),
                'searchTime' => TIME_NOW,
                'searchType' => 'messages',
                'searchHash' => $this->searchHash,
            ],
        ]);
        $resultValues = $objectAction->executeAction();

        return $resultValues['returnValues'];
    }

    private function getAdditionalData(): array
    {
        $additionalData = [];
        foreach ($this->objectTypeNames as $objectTypeName) {
            $objectType = SearchEngine::getInstance()->getObjectType($objectTypeName);
            if (($data = $objectType->getAdditionalData()) !== null) {
                $additionalData[$objectTypeName] = $data;
            }
        }

        return $additionalData;
    }
}
