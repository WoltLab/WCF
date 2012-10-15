<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;

/**
 * Any cronjob should implement this interface.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
interface ICronjob {
	/**
	 * Executes the cronjob.
	 * 
	 * @param	wcf\data\cronjob\Cronjob	$cronjob	Cronjob object with cronjob data
	 */
	public function execute(Cronjob $cronjob);
}
