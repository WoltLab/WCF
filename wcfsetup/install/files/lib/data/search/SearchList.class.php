<?php
namespace wcf\data\search;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of searches.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Search
 *
 * @method	Search		current()
 * @method	Search[]	getObjects()
 * @method	Search|null	search($objectID)
 * @property	Search[]	$objects
 */
class SearchList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Search::class;
}
