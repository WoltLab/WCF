<?php

namespace wcf\command\user;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\event\user\UsersMerging;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\user\merge\UserColumn;
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

        foreach ($this->getUserColumns() as $column) {
            $this->reassign($column);
        }
        $this->updateProfileComments();

        $event = new UsersMerging($this->destinationUser, $this->mergedUsers);
        EventHandler::getInstance()->fire($event);

        foreach ($event->getUserColumns() as $column) {
            $this->reassign($column);
        }

        foreach ($this->mergedUsers as $user) {
            new DeleteUser($user)();
        }
    }

    /**
     * @return list<UserColumn>
     */
    private function getUserColumns(): array
    {
        return [
            new UserColumn('wcf1_poll_option_vote', ignore: true),
            new UserColumn('wcf1_comment', usernameColumn: 'username'),
            new UserColumn('wcf1_comment_response', usernameColumn: 'username'),
            new UserColumn('wcf1_like', ignore: true),
            new UserColumn('wcf1_like', 'objectUserID'),
            new UserColumn('wcf1_like_object', 'objectUserID'),
            new UserColumn('wcf1_user_follow', ignore: true, excludedColumn: 'followUserID'),
            new UserColumn('wcf1_user_follow', 'followUserID', ignore: true, excludedColumn: 'userID'),
            new UserColumn('wcf1_user_ignore', ignore: true, excludedColumn: 'ignoreUserID'),
            new UserColumn('wcf1_user_ignore', 'ignoreUserID', ignore: true, excludedColumn: 'userID'),
            new UserColumn('wcf1_user_object_watch', ignore: true),
            new UserColumn('wcf1_user_activity_event', usernameColumn: 'username'),
            new UserColumn('wcf1_attachment'),
            new UserColumn('wcf1_modification_log', usernameColumn: 'username'),
            new UserColumn('wcf1_user_notification_author', 'authorID', ignore: true),
            new UserColumn('wcf1_article', usernameColumn: 'username'),
            new UserColumn('wcf1_moderation_queue'),
            new UserColumn('wcf1_edit_history_entry', usernameColumn: 'username'),
        ];
    }

    private function reassign(UserColumn $column): void
    {
        (new ReassignUserColumn($column, $this->destinationUser, $this->mergedUserIDs))();
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
