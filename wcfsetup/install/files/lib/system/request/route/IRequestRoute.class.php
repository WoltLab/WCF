<?php
namespace wcf\system\request\route;
use wcf\system\request\IRoute;

/**
 * Default interface for route implementations.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Request
 * @since	3.0
 */
interface IRequestRoute extends IRoute {
	/**
	 * Configures this route to handle either ACP or frontend requests.
	 * 
	 * @param	boolean		$isACP		true if route handles ACP requests
	 */
	public function setIsACP($isACP);
}
