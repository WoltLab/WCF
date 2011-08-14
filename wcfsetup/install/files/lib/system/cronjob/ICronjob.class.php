<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;

/**
 * Any Cronjob should implement this interface.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
interface ICronjob {
	/**
	 * Executes the cronjob.
	 * 
	 * @param	wcf\data\cronjob\Cronjob	$cronjob	Cronjob object with cronjob data
	 */
	public function execute(Cronjob $cronjob);
}
