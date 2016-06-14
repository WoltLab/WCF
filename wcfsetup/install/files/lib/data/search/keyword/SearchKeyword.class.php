<?php
namespace wcf\data\search\keyword;
use wcf\data\DatabaseObject;

/**
 * Represents a search keyword.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Search\Keyword
 *
 * @property-read	integer		$keywordID
 * @property-read	string		$keyword
 * @property-read	integer		$searches
 * @property-read	integer		$lastSearchTime
 */
class SearchKeyword extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'search_keyword';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'keywordID';
}
