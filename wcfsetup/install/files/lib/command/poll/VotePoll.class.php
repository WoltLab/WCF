<?php

namespace wcf\command\poll;

use wcf\data\poll\Poll;
use wcf\data\user\User;
use wcf\event\poll\PollVoted;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Stores the vote of a user in a poll, replacing a previous vote. The given
 * option ids must have been validated against the options of the poll.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class VotePoll
{
    /**
     * @param list<int> $optionIDs
     */
    public function __construct(
        private readonly Poll $poll,
        private readonly User $user,
        private readonly array $optionIDs,
    ) {}

    public function __invoke(): void
    {
        $sql = "SELECT  optionID
                FROM    wcf1_poll_option_vote
                WHERE   pollID = ?
                    AND userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->poll->pollID,
            $this->user->userID,
        ]);
        $previousOptionIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $alreadyVoted = $previousOptionIDs !== [];

        $addedOptionIDs = \array_values(\array_diff($this->optionIDs, $previousOptionIDs));
        $removedOptionIDs = \array_values(\array_diff($previousOptionIDs, $this->optionIDs));

        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            if ($addedOptionIDs !== []) {
                $sql = "INSERT IGNORE INTO  wcf1_poll_option_vote
                                            (pollID, optionID, userID)
                        VALUES              (?, ?, ?)";
                $insertStatement = WCF::getDB()->prepare($sql);

                $sql = "UPDATE  wcf1_poll_option
                        SET     votes = votes + 1
                        WHERE   optionID = ?";
                $updateStatement = WCF::getDB()->prepare($sql);

                foreach ($addedOptionIDs as $optionID) {
                    $insertStatement->execute([
                        $this->poll->pollID,
                        $optionID,
                        $this->user->userID,
                    ]);
                    $updateStatement->execute([$optionID]);
                }
            }

            if ($removedOptionIDs !== []) {
                $sql = "DELETE FROM wcf1_poll_option_vote
                        WHERE       optionID = ?
                                AND userID = ?";
                $deleteStatement = WCF::getDB()->prepare($sql);

                $sql = "UPDATE  wcf1_poll_option
                        SET     votes = votes - 1
                        WHERE   optionID = ?";
                $updateStatement = WCF::getDB()->prepare($sql);

                foreach ($removedOptionIDs as $optionID) {
                    $deleteStatement->execute([
                        $optionID,
                        $this->user->userID,
                    ]);
                    $updateStatement->execute([$optionID]);
                }
            }

            if (!$alreadyVoted) {
                $sql = "UPDATE  wcf1_poll
                        SET     votes = votes + 1
                        WHERE   pollID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$this->poll->pollID]);
            }

            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }

        EventHandler::getInstance()->fire(new PollVoted($this->poll, $this->user, $this->optionIDs));
    }
}
