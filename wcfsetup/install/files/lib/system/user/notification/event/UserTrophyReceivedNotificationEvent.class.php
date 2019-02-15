<?php
namespace wcf\system\user\notification\event;
use wcf\data\trophy\category\TrophyCategory;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyAction;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\UserProfile;
use wcf\system\cache\builder\CategoryCacheBuilder;
use wcf\system\cache\builder\TrophyCacheBuilder;
use wcf\system\user\notification\object\UserTrophyNotificationObject;
use wcf\system\user\notification\TestableUserNotificationEventHandler;

/**
 * Notification event for receiving a user trophy. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * 
 * @method	UserTrophyNotificationObject	getUserNotificationObject()
 */
class UserTrophyReceivedNotificationEvent extends AbstractUserNotificationEvent implements ITestableUserNotificationEvent {
	use TTestableCategorizedUserNotificationEvent;
	use TTestableUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getLanguage()->get('wcf.user.notification.trophy.received.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.trophy.received.message', [
			'userTrophy' => $this->userNotificationObject,
			'author' => $this->author
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsEmailNotification() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->getUserNotificationObject()->getTrophy()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return $this->getUserNotificationObject()->getDecoratedObject()->canSee();
	}
	
	/**
	 * @inheritDoc
	 * @return	UserTrophyNotificationObject[]
	 * @since	3.1
	 */
	public static function getTestObjects(UserProfile $recipient, UserProfile $author) {
		/** @var Trophy $trophy */
		$trophy = (new TrophyAction([], 'create', [
			'data' => [
				'title' => 'Trophy Title',
				'description' => 'Trophy Description',
				'categoryID' => self::createTestCategory(TrophyCategory::OBJECT_TYPE_NAME)->categoryID,
				'type' => Trophy::TYPE_BADGE,
				'isDisabled' => 0,
				'awardAutomatically' => 0,
				'iconName' => 'trophy',
				'iconColor' => 'rgba(255, 255, 255, 1)',
				'badgeColor' => 'rgba(50, 92, 132, 1)'
			]
		]))->executeAction()['returnValues'];
		
		TestableUserNotificationEventHandler::getInstance()->resetCacheBuilder(TrophyCacheBuilder::getInstance());
		TrophyCache::getInstance()->clearCache();
		TrophyCache::getInstance()->init();
		
		TestableUserNotificationEventHandler::getInstance()->resetCacheBuilder(CategoryCacheBuilder::getInstance());
		CategoryCacheBuilder::getInstance()->reset();
		TrophyCategoryCache::getInstance()->init();
		
		/** @var UserTrophy $userTrophy */
		$userTrophy = (new UserTrophyAction([], 'create', [
			'data' => [
				'trophyID' => $trophy->trophyID,
				'userID' => $recipient->userID,
				'description' => 'User Trophy Description',
				'time' => TIME_NOW,
				'useCustomDescription' => 1
			]
		]))->executeAction()['returnValues'];
		
		return [new UserTrophyNotificationObject($userTrophy)];
	}
}
