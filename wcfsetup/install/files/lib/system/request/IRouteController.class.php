<?php
namespace wcf\system\request;
use wcf\data\ITitledObject;

/**
 * Default interface for route controllers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
interface IRouteController extends ITitledObject {
	/**
	 * Returns the id of the object.
	 * 
	 * @return	integer
	 */
	public function getObjectID();
}
