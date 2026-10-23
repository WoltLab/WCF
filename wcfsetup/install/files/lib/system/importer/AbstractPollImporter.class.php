<?php

namespace wcf\system\importer;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\poll\Poll;
use wcf\data\poll\PollBuilder;
use wcf\system\l10n\L10nStorage;

/**
 * Imports polls.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AbstractPollImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = Poll::class;

    /**
     * object type id for poll
     * @var int
     */
    protected $objectTypeID = 0;

    /**
     * object type name
     * @var string
     */
    protected $objectTypeName = '';

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        $builder = PollBuilder::forCreate()
            ->setContainer(
                ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->objectType,
                $data['objectID'] ?? 0
            )
            ->setTime($data['time'] ?? \TIME_NOW)
            ->setQuestion([L10nStorage::MONOLINGUAL => (string)($data['question'] ?? '')]);
        unset($data['objectID'], $data['objectTypeID'], $data['time'], $data['question']);

        foreach ($data as $key => $value) {
            match ($key) {
                'endTime' => $builder->setEndTime($value),
                'isChangeable' => $builder->setIsChangeable((bool)$value),
                'isPublic' => $builder->setIsPublic((bool)$value),
                'sortByVotes' => $builder->setSortByVotes((bool)$value),
                'resultsRequireVote' => $builder->setResultsRequireVote((bool)$value),
                'resultsRequireEnd' => $builder->setResultsRequireEnd((bool)$value),
                'maxVotes' => $builder->setMaxVotes($value),
                'votes' => $builder->setVotes($value),
                default => $builder->setCustomProperty($key, $value),
            };
        }

        $poll = $builder->create();

        ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $poll->pollID);

        return $poll->pollID;
    }
}
