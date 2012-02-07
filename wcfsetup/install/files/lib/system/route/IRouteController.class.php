<?php
namespace wcf\system\route;

/**
 * Default interface for route controllers.
 *
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.route
 * @category 	Community Framework
 */
interface IRouteController {
	/**
	 * Returns the values of the route's components.
	 * 
	 * @return	array
	 */
	public function getRouteComponentValues();
}
