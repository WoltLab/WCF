<?php

namespace wcf\system\search;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\search\mysql\MysqlSearchEngine;
use wcf\system\SingletonFactory;

/**
 * SearchEngine searches for given query in the selected object types.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SearchEngine extends SingletonFactory implements IContextAwareSearchEngine
{
    /**
     * limit for inner search limits
     * @var int
     */
    const INNER_SEARCH_LIMIT = 2500;

    /**
     * list of available object types
     * @var ISearchableObjectType[]|ISearchProvider[]
     */
    protected $availableObjectTypes = [];

    /**
     * search engine object
     * @var ISearchEngine
     */
    protected $searchEngine;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // get available object types
        $availableObjectTypes = ObjectTypeCache::getInstance()
            ->getObjectTypes('com.woltlab.wcf.searchableObjectType');

        // get processors
        foreach ($availableObjectTypes as $key => $objectType) {
            $this->availableObjectTypes[$key] = $objectType->getProcessor();
        }
    }

    /**
     * Returns a list of available object types.
     *
     * @return  ISearchableObjectType[]|ISearchProvider[]
     */
    public function getAvailableObjectTypes()
    {
        return $this->availableObjectTypes;
    }

    /**
     * Returns the object type with the given name.
     *
     * @param string $objectTypeName
     * @return  ISearchableObjectType|ISearchProvider|null
     */
    public function getObjectType($objectTypeName)
    {
        return $this->availableObjectTypes[$objectTypeName] ?? null;
    }

    /**
     * Returns the search engine object.
     *
     * @return  ISearchEngine
     */
    protected function getSearchEngine()
    {
        if ($this->searchEngine === null) {
            if (SEARCH_ENGINE != 'mysql') {
                $className = 'wcf\system\search\\' . SEARCH_ENGINE . '\\' . \ucfirst(SEARCH_ENGINE) . 'SearchEngine';
                if (!\class_exists($className)) {
                    $className = MysqlSearchEngine::class;
                }
            } else {
                $className = MysqlSearchEngine::class;
            }

            $this->searchEngine = \call_user_func([$className, 'getInstance']);
        }

        return $this->searchEngine;
    }

    /**
     * @inheritDoc
     */
    public function search(
        string $q,
        array $objectTypes,
        bool $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        array $additionalConditions = [],
        string $orderBy = 'time DESC',
        int $limit = 1000
    ) {
        return $this->getSearchEngine()
            ->search($q, $objectTypes, $subjectOnly, $searchIndexCondition, $additionalConditions, $orderBy, $limit);
    }

    /**
     * @inheritDoc
     */
    public function searchWithContext(
        string $q,
        array $objectTypes,
        bool $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        array $contextFilter = [],
        array $additionalConditions = [],
        string $orderBy = 'time DESC',
        int $limit = 1000
    ): array {
        $searchEngine = $this->getSearchEngine();
        if ($searchEngine instanceof IContextAwareSearchEngine) {
            return $searchEngine->searchWithContext(
                $q,
                $objectTypes,
                $subjectOnly,
                $searchIndexCondition,
                $contextFilter,
                $additionalConditions,
                $orderBy,
                $limit
            );
        }

        return $searchEngine->search(
            $q,
            $objectTypes,
            $subjectOnly,
            $searchIndexCondition,
            $additionalConditions,
            $orderBy,
            $limit
        );
    }

    /**
     * @inheritDoc
     */
    public function getInnerJoin(
        string $objectTypeName,
        string $q,
        bool $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        string $orderBy = 'time DESC',
        int $limit = 1000
    ) {
        $conditionBuilderClassName = $this->getConditionBuilderClassName();
        if ($searchIndexCondition !== null && !($searchIndexCondition instanceof $conditionBuilderClassName)) {
            throw new SystemException("Search engine '" . SEARCH_ENGINE . "' requires a different condition builder, please use 'SearchEngine::getInstance()->getConditionBuilderClassName()'!");
        }

        return $this->getSearchEngine()
            ->getInnerJoin($objectTypeName, $q, $subjectOnly, $searchIndexCondition, $orderBy, $limit);
    }

    /**
     * @inheritDoc
     */
    public function getInnerJoinWithContext(
        string $objectTypeName,
        string $q,
        bool $subjectOnly = false,
        ?PreparedStatementConditionBuilder $searchIndexCondition = null,
        array $contextFilter = [],
        string $orderBy = 'time DESC',
        int $limit = 1000
    ): array {
        $conditionBuilderClassName = $this->getConditionBuilderClassName();
        if ($searchIndexCondition !== null && !($searchIndexCondition instanceof $conditionBuilderClassName)) {
            throw new SystemException("Search engine '" . SEARCH_ENGINE . "' requires a different condition builder, please use 'SearchEngine::getInstance()->getConditionBuilderClassName()'!");
        }

        $searchEngine = $this->getSearchEngine();
        if ($searchEngine instanceof IContextAwareSearchEngine) {
            return $searchEngine->getInnerJoinWithContext(
                $objectTypeName,
                $q,
                $subjectOnly,
                $searchIndexCondition,
                $contextFilter,
                $orderBy,
                $limit
            );
        }

        return $searchEngine->getInnerJoin(
            $objectTypeName,
            $q,
            $subjectOnly,
            $searchIndexCondition,
            $orderBy,
            $limit
        );
    }

    /**
     * @inheritDoc
     */
    public function getConditionBuilderClassName()
    {
        return $this->getSearchEngine()->getConditionBuilderClassName();
    }

    /**
     * @inheritDoc
     */
    public function removeSpecialCharacters(string $string)
    {
        return $this->getSearchEngine()->removeSpecialCharacters($string);
    }

    /**
     * Returns true if the search backend supports
     * context information for messages.
     *
     * @since 6.0
     */
    public function isContextAware(): bool
    {
        return $this->getSearchEngine() instanceof IContextAwareSearchEngine;
    }
}
