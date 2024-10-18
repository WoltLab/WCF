<?php

namespace wcf\system\worker;

use wcf\data\poll\PollList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Worker implementation for updating polls.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  PollList    getObjectList()
 */
class PollRebuildDataWorker extends AbstractRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = PollList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 10;

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'poll.pollID';
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        $pollIDs = [];
        foreach ($this->getObjectList() as $poll) {
            $pollIDs[] = $poll->pollID;
        }

        if (!empty($pollIDs)) {
            // update poll options
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('poll_option.pollID IN (?)', [$pollIDs]);
            $sql = "UPDATE  wcf1_poll_option poll_option
                    SET     votes = (
                                SELECT  COUNT(*)
                                FROM    wcf1_poll_option_vote
                                WHERE   optionID = poll_option.optionID
                            )
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            // update polls
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('poll.pollID IN (?)', [$pollIDs]);
            $sql = "UPDATE  wcf1_poll poll
                    SET     votes = (
                                SELECT  COUNT(DISTINCT userID)
                                FROM    wcf1_poll_option_vote
                                WHERE   pollID = poll.pollID
                            )
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());
        }
    }
}
