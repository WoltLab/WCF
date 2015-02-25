<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;

/**
 * Cronjob for a hourly system cleanup.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class HourlyCleanUpCronjob extends AbstractCronjob {
	/**
	 * @see	\wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// disable expired paid subscriptions
		if (MODULE_PAID_SUBSCRIPTION) {
			$subscriptionUser = new PaidSubscriptionUserList();
			$subscriptionUser->getConditionBuilder()->add('isActive = ?', array(1));
			$subscriptionUser->getConditionBuilder()->add('endDate > 0 AND endDate < ?', array(TIME_NOW));
			$subscriptionUser->readObjects();
			
			if (count($subscriptionUser->getObjects())) {
				$action = new PaidSubscriptionUserAction(array($subscriptionUser->getObjects()), 'revoke');
			}
		}
	}
}
