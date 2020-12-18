<?php
namespace wcf\system\search\mysql;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\database\DatabaseException;
use wcf\system\exception\SystemException;
use wcf\system\search\AbstractSearchEngine;
use wcf\system\search\SearchEngine;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Search engine using MySQL's FULLTEXT index.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
class MysqlSearchEngine extends AbstractSearchEngine {
	/**
	 * MySQL's minimum word length for fulltext indices
	 * @var	integer
	 */
	protected $ftMinWordLen = null;
	
	/**
	 * @inheritDoc
	 */
	protected $specialCharacters = ['(', ')', '@', '+', '-', '"', '<', '>', '~', '*'];
	
	/**
	 * @inheritDoc
	 */
	public function search($q, array $objectTypes, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, array $additionalConditions = [], $orderBy = 'time DESC', $limit = 1000) {
		// build search query
		$sql = '';
		$parameters = [];
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
		$messages = [];
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($parameters);
		while ($row = $statement->fetchArray()) {
			$messages[] = [
				'objectID' => $row['objectID'],
				'objectType' => $row['objectType']
			];
		}
		
		return $messages;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getInnerJoin($objectTypeName, $q, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, $orderBy = 'time DESC', $limit = 1000) {
		$fulltextCondition = null;
		$relevanceCalc = '';
		if (!empty($q)) {
			$q = $this->parseSearchQuery($q);
			
			$fulltextCondition = new PreparedStatementConditionBuilder(false);
			$fulltextCondition->add("MATCH (subject".(!$subjectOnly ? ', message, metaData' : '').") AGAINST (? IN BOOLEAN MODE)", [$q]);
			
			if ($orderBy == 'relevance ASC' || $orderBy == 'relevance DESC') {
				$relevanceCalc = "MATCH (subject".(!$subjectOnly ? ', message, metaData' : '').") AGAINST ('".escapeString($q)."') + (5 / (1 + POW(LN(1 + (".TIME_NOW." - time) / 2592000), 2))) AS relevance";
			}
		}
		
		$sql = "SELECT		objectID
					".($relevanceCalc ? ','.$relevanceCalc : ", '0' AS relevance")."
			FROM		".SearchIndexManager::getTableName($objectTypeName)."
			WHERE		".($fulltextCondition !== null ? $fulltextCondition : '')."
					".(($searchIndexCondition !== null && $searchIndexCondition->__toString()) ? ($fulltextCondition !== null ? "AND " : '').$searchIndexCondition : '')."
			".(!empty($orderBy) && $fulltextCondition === null ? 'ORDER BY '.$orderBy : '')."
			LIMIT		".($limit == 1000 ? SearchEngine::INNER_SEARCH_LIMIT : $limit);
		
		return [
			'fulltextCondition' => $fulltextCondition,
			'searchIndexCondition' => $searchIndexCondition,
			'sql' => $sql
		];
	}
	
	/**
	 * Manipulates the search term (< and > used as quotation marks):
	 * 
	 * - <test foo> becomes <+test* +foo*>
	 * - <test -foo bar> becomes <+test* -foo* +bar*>
	 * - <test "foo bar"> becomes <+test* +"foo bar">
	 * 
	 * @see	http://dev.mysql.com/doc/refman/5.5/en/fulltext-boolean.html
	 * 
	 * @param	string		$query
	 * @return	string
	 */
	protected function parseSearchQuery($query) {
		$query = StringUtil::trim($query);
		
		// expand search terms with a * unless they're encapsulated with quotes
		$inQuotes = false;
		$previousChar = $tmp = '';
		$controlCharacterOrSpace = false;
		$chars = ['+', '-', '*'];
		$ftMinWordLen = $this->getFulltextMinimumWordLength();
		for ($i = 0, $length = mb_strlen($query); $i < $length; $i++) {
			$char = mb_substr($query, $i, 1);
			
			if ($inQuotes) {
				if ($char == '"') {
					$inQuotes = false;
				}
			}
			else {
				if ($char == '"') {
					$inQuotes = true;
				}
				else {
					if ($char == ' ' && !$controlCharacterOrSpace) {
						$controlCharacterOrSpace = true;
						$tmp .= '*';
					}
					else if (in_array($char, $chars)) {
						$controlCharacterOrSpace = true;
					}
					else {
						$controlCharacterOrSpace = false;
					}
				}
			}
			
			/*
			 * prepend a plus sign (logical AND) if ALL these conditions are given:
			 * 
			 * 1) previous character:
			 *   - is empty (start of string)
			 *   - is a space (MySQL uses spaces to separate words)
			 * 
			 * 2) not within quotation marks
			 * 
			 * 3) current char:
			 *   - is NOT +, - or *
			 */
			if (($previousChar == '' || $previousChar == ' ') && !$inQuotes && !in_array($char, $chars)) {
				// check if the term is shorter than the minimum fulltext word length
				if ($i + $ftMinWordLen <= $length) {
					$term = '';// $char;
					for ($j = $i, $innerLength = $ftMinWordLen + $i; $j < $innerLength; $j++) {
						$currentChar = mb_substr($query, $j, 1);
						if ($currentChar == '"' || $currentChar == ' ' || in_array($currentChar, $chars)) {
							break;
						}
						
						$term .= $currentChar;
					}
					
					if (mb_strlen($term) == $ftMinWordLen) {
						$tmp .= '+';
					}
				}
			}
			
			$tmp .= $char;
			$previousChar = $char;
		}
		
		// handle last char
		if (!$inQuotes && !$controlCharacterOrSpace) {
			$tmp .= '*';
		}
		
		return $tmp;
	}
	
	/**
	 * @inheritDoc
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
				$row = ['Value' => 4];
			}
			
			$this->ftMinWordLen = $row['Value'];
		}
		
		return $this->ftMinWordLen;
	}
}
