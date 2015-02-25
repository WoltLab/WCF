<?php
namespace wcf\data\search;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of searches.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.search
 * @category	Community Framework
 */
class SearchList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\search\Search';
}
