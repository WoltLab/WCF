<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\paid\subscription\user\PaidSubscriptionUserEditor;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\notification\object\PaidSubscriptionUserUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Sends notifications for expiring paid subscriptions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class ExpiringPaidSubscriptionUserCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        // determine when the notification will be send prior to its expiration
        $conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');

        // one week before if the subscription lasts months or years (and not just days)
        $conditionBuilder->add(
            '(paid_subscription.subscriptionLengthUnit <> ? AND paid_subscription_user.endDate < ?)',
            ['d', TIME_NOW + 7 * 24 * 3600]
        );
        // one week before if the subscription lasts for more than two weeks (2 * 7 days)
        $conditionBuilder->add(
            '(paid_subscription.subscriptionLengthUnit = ? AND paid_subscription.subscriptionLength > ? AND paid_subscription_user.endDate < ?)',
            ['d', 2 * 7, TIME_NOW + 7 * 24 * 3600]
        );
        // two days before if the subscription lasts for less than two weeks (2 * 7 days)
        $conditionBuilder->add(
            '(paid_subscription.subscriptionLengthUnit = ? AND paid_subscription.subscriptionLength <= ? AND paid_subscription_user.endDate < ?)',
            ['d', 2 * 7, TIME_NOW + 2 * 24 * 3600]
        );

        $paidSubscriptionUserList = new PaidSubscriptionUserList();
        $paidSubscriptionUserList->sqlJoins .= "
            LEFT JOIN   wcf1_paid_subscription paid_subscription
            ON          paid_subscription.subscriptionID = paid_subscription_user.subscriptionID";
        $paidSubscriptionUserList->getConditionBuilder()->add('paid_subscription_user.endDate <> ?', [0]);
        $paidSubscriptionUserList->getConditionBuilder()->add(
            '(' . $conditionBuilder . ')',
            $conditionBuilder->getParameters()
        );
        $paidSubscriptionUserList->getConditionBuilder()->add('paid_subscription_user.isActive = ?', [1]);
        $paidSubscriptionUserList->getConditionBuilder()->add(
            'paid_subscription_user.sentExpirationNotification = ?',
            [0]
        );
        $paidSubscriptionUserList->readObjects();

        foreach ($paidSubscriptionUserList as $paidSubscriptionUser) {
            UserNotificationHandler::getInstance()->fireEvent(
                'expiring',
                'com.woltlab.wcf.paidSubscription.user',
                new PaidSubscriptionUserUserNotificationObject($paidSubscriptionUser),
                [$paidSubscriptionUser->userID]
            );
        }

        // remember that notification has already been sent (or at least
        // considered if the user does not want to receive notifications)
        WCF::getDB()->beginTransaction();
        foreach ($paidSubscriptionUserList as $paidSubscriptionUser) {
            (new PaidSubscriptionUserEditor($paidSubscriptionUser))->update([
                'sentExpirationNotification' => 1,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
