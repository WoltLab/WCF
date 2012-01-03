<?php
namespace wcf\data\route\component;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes route component-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.route.component
 * @category 	Community Framework
 */
class RouteComponentAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\route\component\RouteComponentEditor';
}
