<?php
namespace wcf\system\user\multifactor;
use wcf\data\user\User;

/**
 * Implementation of the Time-based One-time Password Algorithm (RFC 6238).
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor
 * @since	5.4
 */
class TotpMultifactorMethod implements IMultifactorMethod {
	/**
	 * Returns the number of devices the user set up.
	 */
	public function getStatusText(User $user): string {
		// TODO: Return a proper text.
		return random_int(10000, 99999)." devices configured";
	}
}
