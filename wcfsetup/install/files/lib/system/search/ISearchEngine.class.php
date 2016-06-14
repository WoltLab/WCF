<?php
namespace wcf\system\search;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Default interface for search engines.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
interface ISearchEngine {
	/**
	 * Returns the condition builder class name required to provide conditions for getInnerJoin().
	 * 
	 * @return	string
	 */
	public function getConditionBuilderClassName();
	
	/**
	 * Returns the inner join query and the condition parameters. This method is allowed to return NULL for both the
	 * 'fulltextCondition' and 'searchIndexCondition' index instead of a PreparedStatementConditionBuilder instance:
	 * 
	 * array(
	 * 	'fulltextCondition' => $fulltextCondition || null,
	 * 	'searchIndexCondition' => $searchIndexCondition || null,
	 * 	'sql' => $sql
	 * );
	 * 
	 * @param	string								$objectTypeName
	 * @param	string								$q
	 * @param	boolean								$subjectOnly
	 * @param	\wcf\system\database\util\PreparedStatementConditionBuilder	$searchIndexCondition
	 * @param	string								$orderBy
	 * @param	integer								$limit
	 * @return	array
	 */
	public function getInnerJoin($objectTypeName, $q, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, $orderBy = 'time DESC', $limit = 1000);
	
	/**
	 * Removes engine-specific special characters from a string.
	 * 
	 * @param	string		$string
	 */
	public function removeSpecialCharacters($string);
	
	/**
	 * Searches for the given string and returns the data of the found messages.
	 * 
	 * @param	string								$q
	 * @param	array								$objectTypes
	 * @param	boolean								$subjectOnly
	 * @param	\wcf\system\database\util\PreparedStatementConditionBuilder	$searchIndexCondition
	 * @param	array								$additionalConditions
	 * @param	string								$orderBy
	 * @param	integer								$limit
	 * @return	array
	 */
	public function search($q, array $objectTypes, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, array $additionalConditions = [], $orderBy = 'time DESC', $limit = 1000);
}
