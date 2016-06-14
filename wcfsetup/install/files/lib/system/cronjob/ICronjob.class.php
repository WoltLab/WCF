<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;

/**
 * Any cronjob should implement this interface.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
interface ICronjob {
	/**
	 * Executes the cronjob.
	 * 
	 * @param	\wcf\data\cronjob\Cronjob	$cronjob
	 */
	public function execute(Cronjob $cronjob);
}
