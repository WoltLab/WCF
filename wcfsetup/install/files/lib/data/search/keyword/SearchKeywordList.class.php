<?php
namespace wcf\data\search\keyword;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of keywords.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.search.keyword
 * @category	Community Framework
 *
 * @method	SearchKeyword		current()
 * @method	SearchKeyword[]		getObjects()
 * @method	SearchKeyword|null	search($objectID)
 * @property	SearchKeyword[]		$objects
 */
class SearchKeywordList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = SearchKeyword::class;
}
