<?php

namespace wcf\data\user;

use wcf\data\DatabaseObjectCollection;
use wcf\data\user\avatar\AvatarDecorator;
use wcf\data\user\avatar\DefaultAvatar;
use wcf\data\user\avatar\StaticAvatar;
use wcf\data\user\rank\UserRank;
use wcf\event\user\UserAvatarLoading;
use wcf\system\cache\builder\UserRankCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Represents a collection of users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<User>
 */
class UserCollection extends DatabaseObjectCollection
{
    /**
     * @var array<int, AvatarDecorator>
     */
    private array $avatars = [];

    /**
     * @var array<int, int>
     */
    private array $sessionLastActivityTimes;

    /**
     * ids of the users of this collection that follow a user, grouped by
     * the id of the followed user
     * @var array<int, array<int, int>>
     */
    private array $followerUserIDs = [];

    public function getAvatar(User $user): AvatarDecorator
    {
        if (!isset($this->avatars[$user->userID])) {
            $avatar = null;
            if ($user->disableAvatar === 0 && $this->canSeeAvatar($user)) {
                if ($user->avatarPathname !== null) {
                    $avatar = new StaticAvatar($user->avatarPathname);
                } else {
                    $event = new UserAvatarLoading($user);
                    EventHandler::getInstance()->fire($event);

                    $avatar = $event->getAvatar();
                }
            }

            $this->avatars[$user->userID] = new AvatarDecorator(
                $avatar ?? new DefaultAvatar($user->username ?: '')
            );
        }

        return $this->avatars[$user->userID];
    }

    private function canSeeAvatar(User $user): bool
    {
        return WCF::getUser()->userID === $user->userID
            || WCF::getSession()->hasPermission('user.profile.avatar.canSeeAvatars')
            || (($pending = WCF::getSession()->getPendingUserChange()) !== null && $pending->userID === $user->userID);
    }

    public function getRank(User $user): ?UserRank
    {
        if (\MODULE_USER_RANK === 0 || $user->rankID === null) {
            return null;
        }

        return UserRankCacheBuilder::getInstance()->getRank($user->rankID);
    }

    public function getUserTitle(User $user): string
    {
        if ($user->userTitle !== '') {
            return $user->userTitle;
        }

        $rank = $this->getRank($user);
        if ($rank !== null && $rank->showTitle()) {
            return $rank->getTitle();
        }

        return '';
    }

    /**
     * Returns the time of the last activity, including the activity in a
     * session that has not yet been written to the user.
     */
    public function getLastActivityTime(User $user): int
    {
        $this->loadSessionLastActivityTimes();

        return \max($user->lastActivityTime, $this->sessionLastActivityTimes[$user->userID] ?? 0);
    }

    public function isOnline(User $user): bool
    {
        return $this->getLastActivityTime($user) > (\TIME_NOW - \USER_ONLINE_TIMEOUT)
            && $this->canViewOnlineStatus($user);
    }

    public function canViewOnlineStatus(User $user): bool
    {
        return WCF::getUser()->userID === $user->userID
            || WCF::getSession()->hasPermission('admin.user.canViewInvisible')
            || $user->isAccessible('canViewOnlineStatus');
    }

    /**
     * Returns true if the given user follows the user with the given id.
     */
    public function isFollowing(User $user, int $followUserID): bool
    {
        $this->loadFollowerUserIDs($followUserID);

        return isset($this->followerUserIDs[$followUserID][$user->userID]);
    }

    private function loadSessionLastActivityTimes(): void
    {
        if (isset($this->sessionLastActivityTimes)) {
            return;
        }

        $this->sessionLastActivityTimes = [];

        $userIDs = $this->getRegisteredUserIDs();
        if ($userIDs === []) {
            return;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('userID IN (?)', [$userIDs]);

        $sql = "SELECT      userID, MAX(lastActivityTime) AS lastActivityTime
                FROM        wcf1_session
                {$conditionBuilder}
                GROUP BY    userID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $this->sessionLastActivityTimes = $statement->fetchMap('userID', 'lastActivityTime');
    }

    private function loadFollowerUserIDs(int $followUserID): void
    {
        if (isset($this->followerUserIDs[$followUserID])) {
            return;
        }

        $this->followerUserIDs[$followUserID] = [];

        $userIDs = $this->getRegisteredUserIDs();
        if ($userIDs === [] || $followUserID === 0) {
            return;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('followUserID = ?', [$followUserID]);
        $conditionBuilder->add('userID IN (?)', [$userIDs]);

        $sql = "SELECT  userID
                FROM    wcf1_user_follow
                {$conditionBuilder}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        foreach ($statement->fetchAll(\PDO::FETCH_COLUMN) as $userID) {
            $this->followerUserIDs[$followUserID][$userID] = $userID;
        }
    }

    /**
     * @return int[]
     */
    private function getRegisteredUserIDs(): array
    {
        return \array_values(\array_filter($this->getObjectIDs(), static fn($userID) => $userID !== 0));
    }
}
