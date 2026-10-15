<?php

namespace wcf\system\user\notification\event;

use wcf\data\trophy\category\TrophyCategory;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyBuilder;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\UserProfile;
use wcf\system\cache\builder\TrophyCacheBuilder;
use wcf\system\cache\eager\CategoryCache;
use wcf\system\category\CategoryHandler;
use wcf\system\l10n\L10nStorage;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\user\notification\object\UserTrophyNotificationObject;
use wcf\system\user\notification\TestableUserNotificationEventHandler;

/**
 * Notification event for receiving a user trophy.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserTrophyNotificationObject    getUserNotificationObject()
 */
class UserTrophyReceivedNotificationEvent extends AbstractUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableCategorizedUserNotificationEvent;
    use TTestableUserNotificationEvent;

    #[\Override]
    public function getTitle(): string
    {
        return $this->getLanguage()->get('wcf.user.notification.trophy.received.title');
    }

    #[\Override]
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable('wcf.user.notification.trophy.received.message', [
            'userTrophy' => $this->userNotificationObject,
            'author' => $this->author,
        ]);
    }

    #[\Override]
    public function supportsEmailNotification()
    {
        return false;
    }

    #[\Override]
    public function getLink(): string
    {
        return $this->getUserNotificationObject()->getTrophy()->getLink();
    }

    #[\Override]
    public function checkAccess()
    {
        return $this->getUserNotificationObject()->getDecoratedObject()->canSee();
    }

    /**
     * @return  UserTrophyNotificationObject[]
     */
    #[\Override]
    public static function getTestObjects(UserProfile $recipient, UserProfile $author)
    {
        $trophy = TrophyBuilder::forCreate()
            ->setTitle([L10nStorage::MONOLINGUAL => 'Trophy Title'])
            ->setDescription([L10nStorage::MONOLINGUAL => 'Trophy Description'])
            ->setCategoryID(self::createTestCategory(TrophyCategory::OBJECT_TYPE_NAME)->categoryID)
            ->setType(Trophy::TYPE_BADGE)
            ->setBadge(
                FontAwesomeIcon::fromString('trophy;false')->__toString(),
                'rgba(255, 255, 255, 1)',
                'rgba(50, 92, 132, 1)',
                'rgba(0, 0, 0, 0)'
            )
            ->create();

        TestableUserNotificationEventHandler::getInstance()->resetCacheBuilder(TrophyCacheBuilder::getInstance());
        TrophyCache::getInstance()->clearCache();
        TrophyCache::getInstance()->init();

        TestableUserNotificationEventHandler::getInstance()->resetCacheHandler(new CategoryCache());
        CategoryHandler::getInstance()->reloadCache();
        TrophyCategoryCache::getInstance()->init();

        /** @var UserTrophy $userTrophy */
        $userTrophy = (new UserTrophyAction([], 'create', [
            'data' => [
                'trophyID' => $trophy->trophyID,
                'userID' => $recipient->userID,
                'description' => 'User Trophy Description',
                'time' => \TIME_NOW,
                'useCustomDescription' => 1,
            ],
        ]))->executeAction()['returnValues'];

        return [new UserTrophyNotificationObject($userTrophy)];
    }
}
