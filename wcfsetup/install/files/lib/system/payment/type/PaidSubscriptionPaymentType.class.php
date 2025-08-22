<?php

namespace wcf\system\payment\type;

use wcf\command\paid\subscription\user\ExtendPaidSubscriptionUser;
use wcf\command\paid\subscription\user\RestorePaidSubscriptionUser;
use wcf\command\paid\subscription\user\RevokePaidSubscriptionUser;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLog;
use wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLogAction;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\data\user\User;
use wcf\system\exception\SystemException;

/**
 * IPaymentType implementation for paid subscriptions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PaidSubscriptionPaymentType extends AbstractPaymentType
{
    /**
     * @inheritDoc
     */
    public function processTransaction(
        $paymentMethodObjectTypeID,
        $token,
        $amount,
        $currency,
        $transactionID,
        $status,
        $transactionDetails
    ) {
        $userSubscription = $user = $subscription = null;
        try {
            $tokenParts = \explode(':', $token);
            if (\count($tokenParts) != 2) {
                throw new SystemException('invalid token');
            }
            [$userID, $subscriptionID] = $tokenParts;

            // get user object
            $user = new User(\intval($userID));
            if (!$user->userID) {
                throw new SystemException('invalid user');
            }

            // get subscription object
            $subscription = new PaidSubscription(\intval($subscriptionID));
            if (!$subscription->subscriptionID) {
                throw new SystemException('invalid subscription');
            }

            // search for existing subscription
            $userSubscription = PaidSubscriptionUser::getSubscriptionUser(
                $subscription->subscriptionID,
                $user->userID
            );

            // search log for transaction id
            $logEntry = PaidSubscriptionTransactionLog::getLogByTransactionID(
                $paymentMethodObjectTypeID,
                $transactionID
            );
            if ($logEntry !== null) {
                throw new SystemException('transaction already processed');
            }

            $logMessage = '';
            if ($status == 'completed') {
                // validate payment amount
                if ($amount != $subscription->cost || $currency != $subscription->currency) {
                    throw new SystemException('invalid payment amount');
                }

                // active/extend subscription
                if ($userSubscription === null) {
                    // create new subscription
                    $action = new PaidSubscriptionUserAction([], 'create', [
                        'user' => $user,
                        'subscription' => $subscription,
                    ]);
                    $returnValues = $action->executeAction();
                    $userSubscription = $returnValues['returnValues'];
                } else {
                    (new ExtendPaidSubscriptionUser($userSubscription))();
                }
                $logMessage = 'payment completed';
            }
            if ($status == 'reversed') {
                if ($userSubscription !== null) {
                    (new RevokePaidSubscriptionUser($userSubscription))();
                }
                $logMessage = 'payment reversed';
            }
            if ($status == 'canceled_reversal') {
                if ($userSubscription?->isActive === 1) {
                    (new RestorePaidSubscriptionUser($userSubscription))();
                }
                $logMessage = 'reversal canceled';
            }

            // log success
            $action = new PaidSubscriptionTransactionLogAction([], 'create', [
                'data' => [
                    'subscriptionUserID' => $userSubscription->subscriptionUserID,
                    'userID' => $user->userID,
                    'subscriptionID' => $subscription->subscriptionID,
                    'paymentMethodObjectTypeID' => $paymentMethodObjectTypeID,
                    'logTime' => TIME_NOW,
                    'transactionID' => $transactionID,
                    'logMessage' => $logMessage,
                    'transactionDetails' => \serialize($transactionDetails),
                ],
            ]);
            $action->executeAction();
        } catch (SystemException $e) {
            // log failure
            $action = new PaidSubscriptionTransactionLogAction([], 'create', [
                'data' => [
                    'subscriptionUserID' => $userSubscription !== null ? $userSubscription->subscriptionUserID : null,
                    'userID' => $user !== null ? $user->userID : null,
                    'subscriptionID' => $subscription !== null ? $subscription->subscriptionID : null,
                    'paymentMethodObjectTypeID' => $paymentMethodObjectTypeID,
                    'logTime' => TIME_NOW,
                    'transactionID' => $transactionID,
                    'logMessage' => $e->getMessage(),
                    'transactionDetails' => \serialize($transactionDetails),
                ],
            ]);
            $action->executeAction();
            throw $e;
        }
    }
}
