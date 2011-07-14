<?php
namespace wcf\system\cronjob;
use wcf\system\WCF;

/**
 * Deletes old entries from cronjob log.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class CleanUpCronjobLogCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute(array $data) {
		$sql = "DELETE FROM	wcf".WCF_N."_cronjobs_log
			WHERE		execTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			(TIME_NOW - (86400 * 7))
		));
	}
}
