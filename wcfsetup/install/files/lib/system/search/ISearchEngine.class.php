<?php
namespace wcf\system\search;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Default interface for search engines.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
interface ISearchEngine {
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
	public function search($q, array $objectTypes, $subjectOnly = false, PreparedStatementConditionBuilder $searchIndexCondition = null, array $additionalConditions = array(), $orderBy = 'time DESC', $limit = 1000);
}
