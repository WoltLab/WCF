<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\UserAction;
use wcf\system\WCF;

/**
 * Deletes canceled user accounts.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class UserQuitCronjob extends AbstractCronjob {
	/**
	 * @see	\wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$userIDs = array();
		$sql = "SELECT	userID
			FROM	wcf".WCF_N."_user
			WHERE	quitStarted > ?
				AND quitStarted < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			0,
			(TIME_NOW - 7 * 24 * 3600)
		));
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['userID'];
		}
		
		if (!empty($userIDs)) {
			$action = new UserAction($userIDs, 'delete');
			$action->executeAction();
		}
	}
}
