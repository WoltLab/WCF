<?php

namespace wcf\data\poll;

use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Extends the poll object with functions to create, update and delete polls.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static Poll    create(array $parameters = [])
 * @method      Poll    getDecoratedObject()
 * @mixin       Poll
 */
class PollEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Poll::class;

    /**
     * Calculates poll votes.
     */
    public function calculateVotes()
    {
        // update option votes
        $sql = "UPDATE  wcf1_poll_option poll_option
                SET     poll_option.votes = (
                            SELECT  COUNT(*)
                            FROM    wcf1_poll_option_vote
                            WHERE   optionID = poll_option.optionID
                        )
                WHERE   poll_option.pollID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->pollID]);

        // update total count
        $sql = "UPDATE  wcf1_poll poll
                SET     poll.votes = (
                            SELECT  COUNT(DISTINCT userID)
                            FROM    wcf1_poll_option_vote
                            WHERE   pollID = poll.pollID
                        )
                WHERE   poll.pollID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->pollID]);
    }

    /**
     * Increase votes by one.
     */
    public function increaseVotes()
    {
        $sql = "UPDATE  wcf1_poll
                SET     votes = votes + 1
                WHERE   pollID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->pollID]);
    }
}
