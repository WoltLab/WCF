<?php
namespace wcf\data\route;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of routes.
 * 
 * @author 	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.route
 * @category 	Community Framework
 */
class RouteList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\route\Route';
}
