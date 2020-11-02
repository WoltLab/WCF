<?php
namespace wcf\system\user\multifactor;
use wcf\data\user\User;

/**
 * Handles multifactor authentication for a specific authentication method.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor
 * @since	5.4
 */
interface IMultifactorMethod {
	/**
	 * Returns a human readable status text regarding the set-up status for the given user.
	 * 
	 * An example text could be: "5 backup codes remaining".
	 */
	public function getStatusText(User $user): string;
}
