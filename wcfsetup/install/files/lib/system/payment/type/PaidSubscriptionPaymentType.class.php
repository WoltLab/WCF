<?php
namespace wcf\system\payment\type;
use wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLog;
use wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLogAction;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\User;
use wcf\system\exception\SystemException;

/**
 * IPaymentType implementation for paid subscriptions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Payment\Method
 */
class PaidSubscriptionPaymentType extends AbstractPaymentType {
	/**
	 * @inheritDoc
	 */
	public function processTransaction($paymentMethodObjectTypeID, $token, $amount, $currency, $transactionID, $status, $transactionDetails) {
		$userSubscription = $user = $subscription = null;
		try {
			$tokenParts = explode(':', $token);
			if (count($tokenParts) != 2) {
				throw new SystemException('invalid token');
			}
			list($userID, $subscriptionID) = $tokenParts;
			
			// get user object
			$user = new User(intval($userID));
			if (!$user->userID) {
				throw new SystemException('invalid user');
			}
			
			// get subscription object
			$subscription = new PaidSubscription(intval($subscriptionID));
			if (!$subscription->subscriptionID) {
				throw new SystemException('invalid subscription');
			}
			
			// search for existing subscription
			$userSubscription = PaidSubscriptionUser::getSubscriptionUser($subscription->subscriptionID, $user->userID);
			
			// search log for transaction id
			$logEntry = PaidSubscriptionTransactionLog::getLogByTransactionID($paymentMethodObjectTypeID, $transactionID);
			if ($logEntry !== null) {
				throw new SystemException('transaction already processed');
			}
			
			$logMessage = '';
			if ($status == 'completed') {
				// validate payment amout
				if ($amount != $subscription->cost || $currency != $subscription->currency) {
					throw new SystemException('invalid payment amount');
				}
				
				// active/extend subscription
				if ($userSubscription === null) {
					// create new subscription
					$action = new PaidSubscriptionUserAction([], 'create', [
						'user' => $user,
						'subscription' => $subscription
					]);
					$returnValues = $action->executeAction();
					$userSubscription = $returnValues['returnValues'];
				}
				else {
					// extend existing subscription
					$action = new PaidSubscriptionUserAction([$userSubscription], 'extend');
					$action->executeAction();
				}
				$logMessage = 'payment completed';
			}
			if ($status == 'reversed') {
				if ($userSubscription !== null) {
					// revoke subscription
					$action = new PaidSubscriptionUserAction([$userSubscription], 'revoke');
					$action->executeAction();
				}
				$logMessage = 'payment reversed';
			}
			if ($status == 'canceled_reversal') {
				if ($userSubscription !== null) {
					// restore subscription
					$action = new PaidSubscriptionUserAction([$userSubscription], 'restore');
					$action->executeAction();
				}
				$logMessage = 'reversal canceled';
			}
			
			// log success
			$action = new PaidSubscriptionTransactionLogAction([], 'create', ['data' => [
				'subscriptionUserID' => $userSubscription->subscriptionUserID,
				'userID' => $user->userID,
				'subscriptionID' => $subscription->subscriptionID,
				'paymentMethodObjectTypeID' => $paymentMethodObjectTypeID,
				'logTime' => TIME_NOW,
				'transactionID' => $transactionID,
				'logMessage' => $logMessage,
				'transactionDetails' => serialize($transactionDetails)
			]]);
			$action->executeAction();
		}
		catch (SystemException $e) {
			// log failure
			$action = new PaidSubscriptionTransactionLogAction([], 'create', ['data' => [
				'subscriptionUserID' => ($userSubscription !== null ? $userSubscription->subscriptionUserID : null),
				'userID' => ($user !== null ? $user->userID : null),
				'subscriptionID' => ($subscription !== null ? $subscription->subscriptionID : null),
				'paymentMethodObjectTypeID' => $paymentMethodObjectTypeID,
				'logTime' => TIME_NOW,
				'transactionID' => $transactionID,
				'logMessage' => $e->getMessage(),
				'transactionDetails' => serialize($transactionDetails)
			]]);
			$action->executeAction();
			throw $e;
		}
	}
}
