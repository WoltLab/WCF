<?php

namespace wcf\command\poll;

use wcf\data\poll\Poll;
use wcf\data\poll\PollBuilder;
use wcf\system\WCF;

/**
 * Copies a poll including its options and votes to another poll container.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 * @deprecated 6.3 Only supports the legacy polls that are bound to a content object.
 */
final class CopyPoll
{
    /**
     * @param string $objectType name of the target object type of the definition `com.woltlab.wcf.poll`
     */
    public function __construct(
        private readonly Poll $poll,
        private readonly string $objectType,
        private readonly int $objectID,
    ) {}

    public function __invoke(): Poll
    {
        $sourceOptions = \array_values($this->poll->getOptions());

        $builder = PollBuilder::forCreate()
            ->setContainer($this->objectType, $this->objectID)
            ->setMultilingual($this->poll->isMultilingual === 1)
            ->setQuestion($this->poll->getL10nValues('question'))
            ->setOptions(\array_map(
                static fn($option) => ['optionValue' => $option->getL10nValues('optionValue')],
                $sourceOptions
            ))
            ->setTime($this->poll->time)
            ->setEndTime($this->poll->endTime)
            ->setIsChangeable($this->poll->isChangeable === 1)
            ->setIsPublic($this->poll->isPublic === 1)
            ->setSortByVotes($this->poll->sortByVotes === 1)
            ->setResultsRequireVote($this->poll->resultsRequireVote === 1)
            ->setResultsRequireEnd($this->poll->resultsRequireEnd === 1)
            ->setMinVotes($this->poll->minVotes)
            ->setMaxVotes($this->poll->maxVotes);
        $newPoll = new CreatePoll($builder)();

        // The new options are created in the order of the source options.
        $newOptions = \array_values($newPoll->getOptions());

        $sql = "INSERT INTO wcf1_poll_option_vote
                            (pollID, optionID, userID)
                SELECT      ?, ?, userID
                FROM        wcf1_poll_option_vote
                WHERE       optionID = ?";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($sourceOptions as $index => $sourceOption) {
            $statement->execute([
                $newPoll->pollID,
                $newOptions[$index]->optionID,
                $sourceOption->optionID,
            ]);
        }
        WCF::getDB()->commitTransaction();

        return PollBuilder::forUpdate($newPoll)->recalculateVotes()->update();
    }
}
