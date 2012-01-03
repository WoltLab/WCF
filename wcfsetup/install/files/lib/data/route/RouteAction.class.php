<?php
namespace wcf\data\route;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes route-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.route
 * @category 	Community Framework
 */
class RouteAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\route\RouteEditor';
}
