<?php

namespace wcf\command\user;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\event\user\UsersMerging;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Merges users into a destination user and deletes the merged users afterwards.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MergeUsers
{
    /**
     * @var int[]
     */
    private readonly array $mergedUserIDs;

    /**
     * @param User[] $mergedUsers users that are merged into the destination user
     */
    public function __construct(
        private readonly User $destinationUser,
        private readonly array $mergedUsers,
    ) {
        $this->mergedUserIDs = \array_values(\array_map(
            static fn(User $user) => $user->userID,
            $this->mergedUsers
        ));

        if (\in_array($this->destinationUser->userID, $this->mergedUserIDs, true)) {
            throw new \InvalidArgumentException("The destination user must not be one of the merged users.");
        }
    }

    public function __invoke(): void
    {
        if ($this->mergedUserIDs === []) {
            return;
        }

        $this->updateUserID('wcf1_poll_option_vote', ignore: true);
        $this->updateUserID('wcf1_comment');
        $this->updateUserID('wcf1_comment_response');
        $this->updateProfileComments();
        $this->updateUserID('wcf1_like', ignore: true);
        $this->updateUserID('wcf1_like', 'objectUserID');
        $this->updateUserID('wcf1_like_object', 'objectUserID');
        $this->updateUserID('wcf1_user_follow', ignore: true, excludedColumnName: 'followUserID');
        $this->updateUserID('wcf1_user_follow', 'followUserID', true, 'userID');
        $this->updateUserID('wcf1_user_ignore', ignore: true, excludedColumnName: 'ignoreUserID');
        $this->updateUserID('wcf1_user_ignore', 'ignoreUserID', true, 'userID');
        $this->updateUserID('wcf1_user_object_watch', ignore: true);
        $this->updateUserID('wcf1_user_activity_event');
        $this->updateUserID('wcf1_attachment');
        $this->updateUserID('wcf1_modification_log');
        $this->updateUserID('wcf1_user_notification_author', 'authorID', true);
        $this->updateUserID('wcf1_article');
        $this->updateUserID('wcf1_moderation_queue');
        $this->updateUserID('wcf1_edit_history_entry');

        EventHandler::getInstance()->fire(
            new UsersMerging($this->destinationUser, $this->mergedUsers)
        );

        foreach ($this->mergedUsers as $user) {
            new DeleteUser($user)();
        }
    }

    /**
     * Replaces the ids of the merged users in the given column with the id of
     * the destination user.
     *
     * Rows whose `$excludedColumnName` references the destination user are
     * skipped, e.g. to prevent the destination user from following themselves.
     */
    private function updateUserID(
        string $tableName,
        string $columnName = 'userID',
        bool $ignore = false,
        ?string $excludedColumnName = null
    ): void {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("{$columnName} IN (?)", [$this->mergedUserIDs]);
        if ($excludedColumnName !== null) {
            $conditions->add("{$excludedColumnName} <> ?", [$this->destinationUser->userID]);
        }

        $sql = "UPDATE" . ($ignore ? " IGNORE" : "") . " {$tableName}
                SET     {$columnName} = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUser->userID], $conditions->getParameters()));
    }

    private function updateProfileComments(): void
    {
        $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.comment.commentableContent',
            'com.woltlab.wcf.user.profileComment'
        );

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [$objectType->objectTypeID]);
        $conditions->add("objectID IN (?)", [$this->mergedUserIDs]);

        $sql = "UPDATE  wcf1_comment
                SET     objectID = ?
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([$this->destinationUser->userID], $conditions->getParameters()));
    }
}
