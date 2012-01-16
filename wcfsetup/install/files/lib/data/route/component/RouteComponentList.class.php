<?php
namespace wcf\data\route\component;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of route components.
 * 
 * @author 	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.route.component
 * @category 	Community Framework
 */
class RouteComponentList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\route\component\RouteComponent';
}
