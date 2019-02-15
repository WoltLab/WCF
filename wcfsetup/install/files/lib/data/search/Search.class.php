<?php
namespace wcf\data\search;
use wcf\data\DatabaseObject;

/**
 * Represents a search.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Search
 *
 * @property-read	integer		$searchID	unique id of the search
 * @property-read	integer|null	$userID		id of the user who has done the search or `null` if a guest has done the search
 * @property-read	string		$searchData	serialized array with data and parameters of the seatch
 * @property-read	integer		$searchTime	timestamp of the search
 * @property-read	string		$searchType	type of search, like `messages` or `users`
 * @property-read	string		$searchHash	hash identifying the search for the user to reuse the result within the first 30 minutes
 */
class Search extends DatabaseObject {}
