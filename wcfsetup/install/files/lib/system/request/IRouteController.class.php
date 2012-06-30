<?php
namespace wcf\system\request;
use wcf\data\ITitledDatabaseObject;

/**
 * Default interface for route controllers.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category 	Community Framework
 */
interface IRouteController extends ITitledDatabaseObject {
	/**
	 * Returns the object id.
	 * 
	 * @return	integer
	 */
	public function getID();
}
