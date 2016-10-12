<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;

/**
 * Cronjob for a hourly system cleanup.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class HourlyCleanUpCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// disable expired paid subscriptions
		if (MODULE_PAID_SUBSCRIPTION) {
			$subscriptionUserList = new PaidSubscriptionUserList();
			$subscriptionUserList->getConditionBuilder()->add('isActive = ?', [1]);
			$subscriptionUserList->getConditionBuilder()->add('endDate > 0 AND endDate < ?', [TIME_NOW]);
			$subscriptionUserList->readObjects();
			
			if (count($subscriptionUserList->getObjects())) {
				$action = new PaidSubscriptionUserAction($subscriptionUserList->getObjects(), 'revoke');
				$action->executeAction();
			}
		}
	}
}
