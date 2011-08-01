<?php
namespace wcf\data\page\location;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of page locations.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page.location
 * @category 	Community Framework
 */
class PageLocationList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\page\location\PageLocation';
}
