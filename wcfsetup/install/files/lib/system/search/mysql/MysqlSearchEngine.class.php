<?php
namespace wcf\system\search\mysql;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\database\DatabaseException;
use wcf\system\exception\SystemException;
use wcf\system\search\AbstractSearchEngine;
use wcf\system\search\SearchEngine;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;

/**
 * Search engine using MySQL's FULLTEXT index.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
class MysqlSearchEngine extends AbstractSearchEngine {
	/**
	 * MySQL's minimum word length for fulltext indices
	 * @var	integer
	 */
	protected $ftMinWordLen = null;
	
	/**
	 * @see	\wcf\system\search\AbstractSearchEngine::$specialCharacters
	 */
	protected $specialCharacters = array('(', ')', '@', '+', '-', '"', '<', '>', '~', '*');
	
	/**
	 * @see	\wcf\system\search\ISearchEngine::search()
	 */
	public function search($q, array $objectTypes, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, array $additionalConditions = array(), $orderBy = 'time DESC', $limit = 1000) {
		// build search query
		$sql = '';
		$parameters = array();
		foreach ($objectTypes as $objectTypeName) {
			$objectType = SearchEngine::getInstance()->getObjectType($objectTypeName);
			
			if (!empty($sql)) $sql .= "\nUNION ALL\n";
			$additionalConditionsConditionBuilder = (isset($additionalConditions[$objectTypeName]) ? $additionalConditions[$objectTypeName] : null);
			
			$query = $objectType->getOuterSQLQuery($q, $searchIndexCondition, $additionalConditionsConditionBuilder);
			if (empty($query)) {
				$query = "SELECT	".$objectType->getIDFieldName()." AS objectID,
							".$objectType->getSubjectFieldName()." AS subject,
							".$objectType->getTimeFieldName()." AS time,
							".$objectType->getUsernameFieldName()." AS username,
							'".$objectTypeName."' AS objectType
							".($orderBy == 'relevance ASC' || $orderBy == 'relevance DESC' ? ',search_index.relevance' : '')."
					FROM		".$objectType->getTableName()."
					INNER JOIN	(
								{WCF_SEARCH_INNER_JOIN}
							) search_index
					ON		(".$objectType->getIDFieldName()." = search_index.objectID)
					".$objectType->getJoins()."
					".(isset($additionalConditions[$objectTypeName]) ? $additionalConditions[$objectTypeName] : '');
			}
			
			if (mb_strpos($query, '{WCF_SEARCH_INNER_JOIN}')) {
				$innerJoin = $this->getInnerJoin($objectTypeName, $q, $subjectOnly, $searchIndexCondition, $orderBy, $limit);
				
				$query = str_replace('{WCF_SEARCH_INNER_JOIN}', $innerJoin['sql'], $query);
				if ($innerJoin['fulltextCondition'] !== null) $parameters = array_merge($parameters, $innerJoin['fulltextCondition']->getParameters());
			}
			
			if ($searchIndexCondition !== null) $parameters = array_merge($parameters, $searchIndexCondition->getParameters());
			if (isset($additionalConditions[$objectTypeName])) $parameters = array_merge($parameters, $additionalConditions[$objectTypeName]->getParameters());
			
			$sql .= $query;
		}
		if (empty($sql)) {
			throw new SystemException('no object types given');
		}
		
		if (!empty($orderBy)) {
			$sql .= " ORDER BY " . $orderBy;
		}
		
		// send search query
		$messages = array();
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($parameters);
		while ($row = $statement->fetchArray()) {
			$messages[] = array(
				'objectID' => $row['objectID'],
				'objectType' => $row['objectType']
			);
		}
		
		return $messages;
	}
	
	/**
	 * @see	\wcf\system\search\ISearchEngine::getInnerJoin()
	 */
	public function getInnerJoin($objectTypeName, $q, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, $orderBy = 'time DESC', $limit = 1000) {
		$fulltextCondition = null;
		$relevanceCalc = '';
		if (!empty($q)) {
			$q = $this->parseSearchQuery($q);
			
			$fulltextCondition = new PreparedStatementConditionBuilder(false);
			$fulltextCondition->add("MATCH (subject".(!$subjectOnly ? ', message, metaData' : '').") AGAINST (? IN BOOLEAN MODE)", array($q));
			
			if ($orderBy == 'relevance ASC' || $orderBy == 'relevance DESC') {
				$relevanceCalc = "MATCH (subject".(!$subjectOnly ? ', message, metaData' : '').") AGAINST ('".escapeString($q)."') + (5 / (1 + POW(LN(1 + (".TIME_NOW." - time) / 2592000), 2))) AS relevance";
			}
		}
		
		$sql = "SELECT		objectID
					".($relevanceCalc ? ','.$relevanceCalc : '')."
			FROM		".SearchIndexManager::getTableName($objectTypeName)."
			WHERE		".($fulltextCondition !== null ? $fulltextCondition : '')."
					".(($searchIndexCondition !== null && $searchIndexCondition->__toString()) ? ($fulltextCondition !== null ? "AND " : '').$searchIndexCondition : '')."
			".(!empty($orderBy) && $fulltextCondition === null ? 'ORDER BY '.$orderBy : '')."
			LIMIT		1000";
		
		return array(
			'fulltextCondition' => $fulltextCondition,
			'searchIndexCondition' => $searchIndexCondition,
			'sql' => $sql
		);
	}
	
	/**
	 * @see	\wcf\system\search\AbstractSearchEngine::getFulltextMinimumWordLength()
	 */
	protected function getFulltextMinimumWordLength() {
		if ($this->ftMinWordLen === null) {
			$sql = "SHOW VARIABLES LIKE 'ft_min_word_len'";
			
			try {
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute();
				$row = $statement->fetchArray();
			}
			catch (DatabaseException $e) {
				// fallback if user is disallowed to issue 'SHOW VARIABLES'
				$row = array('Value' => 4);
			}
			
			$this->ftMinWordLen = $row['Value'];
		}
		
		return $this->ftMinWordLen;
	}
}
