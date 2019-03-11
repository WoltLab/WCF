<?php
namespace wcf\system\user\notification\event;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionAction;
use wcf\data\user\UserProfile;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\PaidSubscriptionUserUserNotificationObject;
use wcf\system\WCF;

/**
 * Notification event for expiring paid subscriptions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.1
 * 
 * @method	PaidSubscriptionUserUserNotificationObject	getUserNotificationObject()
 */
class ExpiringPaidSubscriptionUserUserNotificationEvent extends AbstractUserNotificationEvent implements ITestableUserNotificationEvent {
	use TTestableUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('PaidSubscriptionList', ['forceFrontend' => true]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getLanguage()->getDynamicVariable('wcf.paidSubscription.expiringSubscription.notification.message', [
			'author' => $this->author,
			'notification' => $this->notification,
			'userNotificationObject' => $this->getUserNotificationObject()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return [
			'template' => 'email_notification_expiringPaidSubscription',
			'application' => 'wcf',
			'variables' => [
				'notification' => $this->notification,
				'subscription' => $this->getUserNotificationObject()
			]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getLanguage()->get('wcf.paidSubscription.expiringSubscription.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible() {
		$userSubscriptionList = new PaidSubscriptionUserList();
		$userSubscriptionList->getConditionBuilder()->add('userID = ?', [WCF::getUser()->userID]);
		$userSubscriptionList->getConditionBuilder()->add('isActive = ?', [1]);
		
		return $userSubscriptionList->countObjects() > 0;
	}
	
	/**
	 * @inheritDoc
	 * @return	PaidSubscriptionUserUserNotificationObject[]
	 * @since	3.1
	 */
	public static function getTestObjects(UserProfile $recipient, UserProfile $author) {
		/** @var PaidSubscription $paidSubscription */
		$paidSubscription = (new PaidSubscriptionAction([], 'create', [
			'data' => [
				'title' => 'Test Subscription'
			]
		]))->executeAction()['returnValues'];
		
		/** @var PaidSubscriptionUser $paidSubscriptionUser */
		$paidSubscriptionUser = (new PaidSubscriptionUserAction([], 'create', [
			'data' => [
				'startDate' => TIME_NOW - 24 * 24 * 60 * 60,
				'endDate' => TIME_NOW + 24 * 60 * 60,
				'isActive' => 1,
				'sentExpirationNotification' => 0
			],
			'subscription' => $paidSubscription,
			'user' => $recipient
		]))->executeAction()['returnValues'];
		
		$paidSubscriptionUser->setSubscription($paidSubscription);
		
		return [new PaidSubscriptionUserUserNotificationObject($paidSubscriptionUser)];
	}
}
