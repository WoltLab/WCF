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
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
class SearchEngine extends SingletonFactory implements ISearchEngine {
	/**
	 * limit for inner search limits
	 * @var	integer
	 */
	const INNER_SEARCH_LIMIT = 2500;
	
	/**
	 * list of available object types
	 * @var	ISearchableObjectType[]
	 */
	protected $availableObjectTypes = [];
	
	/**
	 * search engine object
	 * @var	\wcf\system\search\ISearchEngine
	 */
	protected $searchEngine = null;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// get available object types
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.searchableObjectType');
		
		// get processors
		foreach ($this->availableObjectTypes as &$objectType) {
			$objectType = $objectType->getProcessor();
		}
	}
	
	/**
	 * Returns a list of available object types.
	 * 
	 * @return	ISearchableObjectType[]
	 */
	public function getAvailableObjectTypes() {
		return $this->availableObjectTypes;
	}
	
	/**
	 * Returns the object type with the given name.
	 * 
	 * @param	string		$objectTypeName
	 * @return	ISearchableObjectType|null
	 */
	public function getObjectType($objectTypeName) {
		if (isset($this->availableObjectTypes[$objectTypeName])) {
			return $this->availableObjectTypes[$objectTypeName];
		}
		
		return null;
	}
	
	/**
	 * Returns the search engine object.
	 * 
	 * @return	\wcf\system\search\ISearchEngine
	 */
	protected function getSearchEngine() {
		if ($this->searchEngine === null) {
			$className = '';
			if (SEARCH_ENGINE != 'mysql') {
				$className = 'wcf\system\search\\'.SEARCH_ENGINE.'\\'.ucfirst(SEARCH_ENGINE).'SearchEngine';
				if (!class_exists($className)) {
					$className = '';
				}
			}
			
			// fallback to MySQL
			if (empty($className)) {
				$className = MysqlSearchEngine::class;
			}
			
			$this->searchEngine = call_user_func([$className, 'getInstance']);
		}
		
		return $this->searchEngine;
	}
	
	/**
	 * @inheritDoc
	 */
	public function search($q, array $objectTypes, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, array $additionalConditions = [], $orderBy = 'time DESC', $limit = 1000) {
		return $this->getSearchEngine()->search($q, $objectTypes, $subjectOnly, $searchIndexCondition, $additionalConditions, $orderBy, $limit);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getInnerJoin($objectTypeName, $q, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, $orderBy = 'time DESC', $limit = 1000) {
		$conditionBuilderClassName = $this->getConditionBuilderClassName();
		if ($searchIndexCondition !== null && !($searchIndexCondition instanceof $conditionBuilderClassName)) {
			throw new SystemException("Search engine '" . SEARCH_ENGINE . "' requires a different condition builder, please use 'SearchEngine::getInstance()->getConditionBuilderClassName()'!");
		}
		
		return $this->getSearchEngine()->getInnerJoin($objectTypeName, $q, $subjectOnly, $searchIndexCondition, $orderBy, $limit);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionBuilderClassName() {
		return $this->getSearchEngine()->getConditionBuilderClassName();
	}
	
	/**
	 * @inheritDoc
	 */
	public function removeSpecialCharacters($string) {
		return $this->getSearchEngine()->removeSpecialCharacters($string);
	}
}
