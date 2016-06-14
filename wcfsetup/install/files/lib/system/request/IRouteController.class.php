<?php
namespace wcf\system\request;
use wcf\data\ITitledObject;

/**
 * Default interface for route controllers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Request
 */
interface IRouteController extends ITitledObject {
	/**
	 * Returns the id of the object.
	 * 
	 * @return	integer
	 */
	public function getObjectID();
}
