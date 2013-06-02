<?php
namespace wcf\data\search\keyword;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of keywords.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	data.search.keyword
 * @category	Community Framework
 */
class SearchKeywordList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\search\keyword\SearchKeyword';
}
