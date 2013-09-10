<?php
namespace wcf\system\search;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * SearchEngine searches for given query in the selected object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
class SearchEngine extends SingletonFactory {
	/**
	 * list of available object types
	 * @var	array
	 */
	protected $availableObjectTypes = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
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
	 * @return	array
	 */
	public function getAvailableObjectTypes() {
		return $this->availableObjectTypes;
	}
	
	/**
	 * Returns the object type with the given name.
	 * 
	 * @param	string		$objectTypeName
	 * @return	wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeName) {
		if (isset($this->availableObjectTypes[$objectTypeName])) {
			return $this->availableObjectTypes[$objectTypeName];
		}
		
		return null;
	}
	
	/**
	 * Searches for the given string and returns the data of the found messages.
	 * 
	 * @param	string								$q
	 * @param	array								$objectTypes
	 * @param	boolean								$subjectOnly
	 * @param	wcf\system\database\util\PreparedStatementConditionBuilder	$searchIndexCondition
	 * @param	array								$additionalConditions
	 * @param	string								$orderBy
	 * @param	integer								$limit
	 * @return	array
	 */
	public function search($q, array $objectTypes, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, array $additionalConditions = array(), $orderBy = 'time DESC', $limit = 1000) {
		// handle sql types
		$fulltextCondition = null;
		$relevanceCalc = '';
		if (!empty($q)) {
			// expand search terms with a * unless they're encapsulated with quotes
			$inQuotes = false;
			$tmp = '';
			$controlCharacterOrSpace = false;
			$chars = array('+', '-', '*');
			for ($i = 0, $length = mb_strlen($q); $i < $length; $i++) {
				$char = $q[$i];
				
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
				
				$tmp .= $char;
			}
			
			// handle last char
			if (!$inQuotes && !$controlCharacterOrSpace) {
				$tmp .= '*';
			}
			$q = $tmp;
			
			$fulltextCondition = new PreparedStatementConditionBuilder(false);
			switch (WCF::getDB()->getDBType()) {
				case 'wcf\system\database\MySQLDatabase':
					$fulltextCondition->add("MATCH (subject".(!$subjectOnly ? ', message, metaData' : '').") AGAINST (? IN BOOLEAN MODE)", array($q));
				break;
				
				case 'wcf\system\database\PostgreSQLDatabase':
					// replace * with :*
					$q = str_replace('*', ':*', $q);
					
					$fulltextCondition->add("fulltextIndex".($subjectOnly ? "SubjectOnly" : '')." @@ to_tsquery(?)", array($q));
				break;
				
				default:
					throw new SystemException("your database type doesn't support fulltext search");
			}
			
			if ($orderBy == 'relevance ASC' || $orderBy == 'relevance DESC') {
				switch (WCF::getDB()->getDBType()) {
					case 'wcf\system\database\MySQLDatabase':
						$relevanceCalc = "MATCH (subject".(!$subjectOnly ? ', message, metaData' : '').") AGAINST ('".escapeString($q)."') + (5 / (1 + POW(LN(1 + (".TIME_NOW." - time) / 2592000), 2))) AS relevance";
					break;
					
					case 'wcf\system\database\PostgreSQLDatabase':
						$relevanceCalc = "ts_rank_cd(fulltextIndex".($subjectOnly ? "SubjectOnly" : '').", '".escapeString($q)."') AS relevance";
					break;
				}
			}
		}
		
		// build search query
		$sql = '';
		$parameters = array();
		foreach ($objectTypes as $objectTypeName) {
			$objectType = $this->getObjectType($objectTypeName);
			if (!empty($sql)) $sql .= "\nUNION\n";
			$additionalConditionsConditionBuilder = (isset($additionalConditions[$objectTypeName]) ? $additionalConditions[$objectTypeName] : null);
			if (($specialSQL = $objectType->getSpecialSQLQuery($fulltextCondition, $searchIndexCondition, $additionalConditionsConditionBuilder, $orderBy))) {
				$sql .= "(".$specialSQL.")";
			}
			else {
				$sql .= "(
					SELECT		".$objectType->getIDFieldName()." AS objectID,
							".$objectType->getSubjectFieldName()." AS subject,
							".$objectType->getTimeFieldName()." AS time,
							".$objectType->getUsernameFieldName()." AS username,
							'".$objectTypeName."' AS objectType
							".($relevanceCalc ? ',search_index.relevance' : '')."
					FROM		".$objectType->getTableName()."
					INNER JOIN 	(
								SELECT		objectID
										".($relevanceCalc ? ','.$relevanceCalc : '')."
								FROM		wcf".WCF_N."_search_index
								WHERE		".($fulltextCondition !== null ? $fulltextCondition : '')."
										".(($searchIndexCondition !== null && $searchIndexCondition->__toString()) ? ($fulltextCondition !== null ? "AND " : '').$searchIndexCondition : '')."
										AND objectTypeID = ".$objectType->objectTypeID."
								".(!empty($orderBy) && $fulltextCondition === null ? 'ORDER BY '.$orderBy : '')."
								LIMIT		1000
							) search_index
					ON 		(".$objectType->getIDFieldName()." = search_index.objectID)
					".$objectType->getJoins()."
					".(isset($additionalConditions[$objectTypeName]) ? $additionalConditions[$objectTypeName] : '')."
				)";
			}
			
			if ($fulltextCondition !== null) $parameters = array_merge($parameters, $fulltextCondition->getParameters());
			if ($searchIndexCondition !== null) $parameters = array_merge($parameters, $searchIndexCondition->getParameters());
			if (isset($additionalConditions[$objectTypeName])) $parameters = array_merge($parameters, $additionalConditions[$objectTypeName]->getParameters());
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
}
