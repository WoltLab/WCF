<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\event\user\UserRenamed;
use wcf\event\user\UserUpdated;
use wcf\system\event\EventHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\session\SessionHandler;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\user\rename\UsernameColumn;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Updates a user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateUser
{
    public function __construct(
        private readonly UserBuilder $builder,
        private readonly bool $ignoreUserGroupAssignments = false,
    ) {}

    public function __invoke(): User
    {
        $oldUser = $this->builder->getObject();
        $this->builder->update();

        // The object returned by the builder lacks the changes to the user options.
        $user = new User($oldUser->userID);

        if ($user->username !== $oldUser->username) {
            $this->rename($user, $oldUser->username);
        }

        if ($this->builder->isPasswordChanged()) {
            SessionHandler::getInstance()->deleteUserSessionsExcept(
                $user,
                SessionHandler::getInstance()->sessionID
            );
        }

        if ($user->userID === WCF::getUser()->userID && $user->languageID !== $oldUser->languageID) {
            WCF::setLanguage($user->languageID);
        }

        if ($this->builder->getLanguageIDs() !== null) {
            UserStorageHandler::getInstance()->reset([$user->userID], 'languageIDs');
        }

        $groupsChanged = $this->builder->getGroupIDs() !== null;
        if ($groupsChanged) {
            UserStorageHandler::getInstance()->reset([$user->userID], 'groupIDs');
            $this->resetModerationQueueAssignments($user);
        }

        // The automatic group assignments and the user rank can depend on user options.
        if ($groupsChanged || $this->builder->getUserOptions() !== null) {
            if (!$this->ignoreUserGroupAssignments) {
                UserGroupAssignmentHandler::getInstance()->checkUsers([$user->userID]);
            }

            if (\MODULE_USER_RANK !== 0) {
                (new RefreshUserRanks([$user]))();
            }
        }

        if ($groupsChanged && \MODULE_USERS_ONLINE !== 0) {
            (new RefreshUserOnlineMarkings([$user]))();
        }

        EventHandler::getInstance()->fire(new UserUpdated($user, $this->builder));

        return $user;
    }

    /**
     * Removes the assignments to the moderation queue, the access to the queue
     * entries depends on the permissions of the user groups.
     */
    private function resetModerationQueueAssignments(User $user): void
    {
        $sql = "DELETE FROM wcf1_moderation_queue_to_user
                WHERE       userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$user->userID]);

        ModerationQueueManager::getInstance()->resetModerationCount($user->userID);
    }

    /**
     * Updates the username that is stored alongside the user id in other tables.
     */
    private function rename(User $user, string $oldUsername): void
    {
        $event = new UserRenamed($user, $oldUsername);
        EventHandler::getInstance()->fire($event);

        WCF::getDB()->beginTransaction();
        foreach ([...$this->getUsernameColumns(), ...$event->getUsernameColumns()] as $column) {
            (new UpdateUsernameColumn($column, $user))();
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * @return list<UsernameColumn>
     */
    private function getUsernameColumns(): array
    {
        return [
            new UsernameColumn('wcf1_article'),
            new UsernameColumn('wcf1_comment'),
            new UsernameColumn('wcf1_comment_response'),
            new UsernameColumn('wcf1_media'),
            new UsernameColumn('wcf1_modification_log'),
        ];
    }
}
