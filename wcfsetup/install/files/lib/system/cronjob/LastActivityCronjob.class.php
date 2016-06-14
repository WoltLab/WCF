<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Updates the last activity timestamp in the user table.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class LastActivityCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
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
