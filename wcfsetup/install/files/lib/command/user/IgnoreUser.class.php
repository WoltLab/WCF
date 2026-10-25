<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\ignore\UserIgnore;
use wcf\data\user\ignore\UserIgnoreBuilder;
use wcf\event\user\UserIgnored;
use wcf\system\event\EventHandler;
use wcf\system\user\command\Unfollow;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Saves that one user ignores another user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class IgnoreUser
{
    public function __construct(
        private readonly User $user,
        private readonly User $target,
        /** @var UserIgnore::TYPE_BLOCK_DIRECT_CONTACT|UserIgnore::TYPE_HIDE_MESSAGES */
        private readonly int $type
    ) {}

    public function __invoke(): void
    {
        $this->ignoreUser($this->user, $this->target, $this->type);

        new Unfollow($this->target, $this->user)();

        $this->resetStorage($this->user, $this->target);

        $event = new UserIgnored($this->user, $this->target, $this->type);
        EventHandler::getInstance()->fire($event);
    }

    private function resetStorage(User $user, User $target): void
    {
        UserStorageHandler::getInstance()->reset([$user->userID], 'ignoredUserIDs');
        UserStorageHandler::getInstance()->reset([$target->userID], 'ignoredByUserIDs');
    }

    /**
     * @param UserIgnore::TYPE_BLOCK_DIRECT_CONTACT|UserIgnore::TYPE_HIDE_MESSAGES $type
     */
    private function ignoreUser(User $user, User $target, int $type): void
    {
        $ignore = $this->getIgnore($user, $target);
        if ($ignore !== null) {
            UserIgnoreBuilder::forUpdate($ignore)
                ->setType($type)
                ->setTime(\TIME_NOW)
                ->update();

            return;
        }

        UserIgnoreBuilder::forCreate()
            ->setUserID($user->userID)
            ->setIgnoreUserID($target->userID)
            ->setType($type)
            ->setTime(\TIME_NOW)
            ->createOrIgnore();
    }

    private function getIgnore(User $user, User $target): ?UserIgnore
    {
        $sql = "SELECT  *
                FROM    wcf1_user_ignore
                WHERE   userID = ?
                    AND ignoreUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $user->userID,
            $target->userID,
        ]);

        return $statement->fetchSingleObject(UserIgnore::class);
    }
}
