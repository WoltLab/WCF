<?php
namespace wcf\system\request;

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
interface IRouteController {
	/**
	 * Returns the object id.
	 * 
	 * @return	integer
	 */
	public function getID();
	
	/**
	 * Returns the object title.
	 * 
	 * @return	string
	 */
	public function getTitle();
}
