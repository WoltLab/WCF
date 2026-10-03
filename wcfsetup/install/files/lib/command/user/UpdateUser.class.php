<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\data\user\UserProfileAction;
use wcf\event\user\UserRenamed;
use wcf\event\user\UserUpdated;
use wcf\system\event\EventHandler;
use wcf\system\session\SessionHandler;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
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
        }

        // The automatic group assignments and the user rank can depend on user options.
        if ($groupsChanged || $this->builder->getUserOptions() !== null) {
            UserGroupAssignmentHandler::getInstance()->checkUsers([$user->userID]);

            if (\MODULE_USER_RANK !== 0) {
                $action = new UserProfileAction([$user], 'updateUserRank');
                $action->executeAction();
            }
        }

        if ($groupsChanged && \MODULE_USERS_ONLINE !== 0) {
            $action = new UserProfileAction([$user], 'updateUserOnlineMarking');
            $action->executeAction();
        }

        EventHandler::getInstance()->fire(new UserUpdated($user, $this->builder));

        return $user;
    }

    /**
     * Updates the username that is stored alongside the user id in other tables.
     */
    private function rename(User $user, string $oldUsername): void
    {
        WCF::getDB()->beginTransaction();
        foreach (['wcf1_article', 'wcf1_comment', 'wcf1_comment_response', 'wcf1_media', 'wcf1_modification_log'] as $tableName) {
            $sql = "UPDATE  {$tableName}
                    SET     username = ?
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $user->username,
                $user->userID,
            ]);
        }
        WCF::getDB()->commitTransaction();

        EventHandler::getInstance()->fire(new UserRenamed($user, $oldUsername));
    }
}
