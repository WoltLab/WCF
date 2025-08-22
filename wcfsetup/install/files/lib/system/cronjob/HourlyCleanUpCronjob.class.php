<?php

namespace wcf\system\cronjob;

use wcf\command\paid\subscription\user\RevokePaidSubscriptionUser;
use wcf\data\cronjob\Cronjob;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;

/**
 * Cronjob for a hourly system cleanup.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class HourlyCleanUpCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        // disable expired paid subscriptions
        if (MODULE_PAID_SUBSCRIPTION) {
            $subscriptionUserList = new PaidSubscriptionUserList();
            $subscriptionUserList->getConditionBuilder()->add('isActive = ?', [1]);
            $subscriptionUserList->getConditionBuilder()->add('endDate > 0 AND endDate < ?', [TIME_NOW]);
            $subscriptionUserList->readObjects();

            foreach ($subscriptionUserList->getObjects() as $subscriptionUser) {
                (new RevokePaidSubscriptionUser($subscriptionUser))();
            }
        }
    }
}
