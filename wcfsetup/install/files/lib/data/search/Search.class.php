<?php
namespace wcf\data\search;
use wcf\data\DatabaseObject;

/**
 * Represents a search.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Search
 *
 * @property-read	integer		$searchID
 * @property-read	integer|null	$userID
 * @property-read	string		$searchData
 * @property-read	integer		$searchTime
 * @property-read	string		$searchType
 * @property-read	string		$searchHash
 */
class Search extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'search';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'searchID';
}
