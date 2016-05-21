<?php
namespace wcf\data\search;
use wcf\data\DatabaseObject;

/**
 * Represents a search.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.search
 * @category	Community Framework
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
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'search';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'searchID';
}
