<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Updates the last activity timestamp in the user table.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class LastActivityCronjob extends AbstractCronjob {
	/**
	 * @see	\wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$sql = "UPDATE	wcf".WCF_N."_user user_table,
				wcf".WCF_N."_session session
			SET	user_table.lastActivityTime = session.lastActivityTime
			WHERE	user_table.userID = session.userID
				AND session.userID <> 0";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
	}
}
