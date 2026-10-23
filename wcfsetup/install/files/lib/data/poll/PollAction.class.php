<?php

namespace wcf\data\poll;

use wcf\command\poll\CopyPoll;
use wcf\command\poll\CreatePoll;
use wcf\command\poll\DeletePolls;
use wcf\command\poll\UpdatePoll;
use wcf\command\poll\VotePoll;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IGroupedUserListAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\l10n\L10nStorage;
use wcf\system\poll\PollManager;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes poll-related actions.
 *
 * @author  Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Poll, PollEditor>
 * @deprecated 6.3 Use `PollBuilder`, the commands and the RPC endpoints instead.
 */
class PollAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getGroupedUserList'];

    /**
     * @inheritDoc
     */
    protected $className = PollEditor::class;

    /**
     * poll object
     * @var ?PollEditor
     */
    protected $poll;

    #[\Override]
    public function create()
    {
        $builder = PollBuilder::forCreate()
            ->setTime($this->parameters['data']['time'] ?? \TIME_NOW)
            ->setOptions($this->getOptionsFromParameter());
        $this->applyData($builder, $this->parameters['data']);

        return (new CreatePoll($builder))();
    }

    #[\Override]
    public function update()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $pollEditor) {
            $builder = PollBuilder::forUpdate($pollEditor->getDecoratedObject());
            $this->applyData($builder, $this->parameters['data'] ?? []);
            if (isset($this->parameters['options'])) {
                $builder->setOptions($this->getOptionsFromParameter());
            }

            (new UpdatePoll($builder))();
        }
    }

    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        (new DeletePolls(\array_map(
            static fn(PollEditor $editor) => $editor->getDecoratedObject(),
            $this->getObjects()
        )))();

        return \count($this->objects);
    }

    /**
     * Maps the legacy `data` parameter to the given builder.
     *
     * @param array<string, mixed> $data
     */
    private function applyData(PollBuilder $builder, array $data): void
    {
        foreach ($data as $key => $value) {
            match ($key) {
                'objectTypeID' => $builder->setContainer(
                    ObjectTypeCache::getInstance()->getObjectType($value)->objectType,
                    $data['objectID'] ?? 0
                ),
                'objectID' => $builder->setObjectID($value),
                'question' => $builder->setQuestion([L10nStorage::MONOLINGUAL => (string)$value]),
                'time' => $builder->setTime($value),
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
    }

    /**
     * Maps the legacy `options` parameter (`showOrder => [optionID, optionValue]`)
     * to the options of the builder.
     *
     * @return list<array{optionID: int, optionValue: array<int, string>}>
     */
    private function getOptionsFromParameter(): array
    {
        $options = $this->parameters['options'] ?? [];
        \ksort($options);

        return \array_values(\array_map(
            static fn(array $option) => [
                'optionID' => (int)($option['optionID'] ?? 0),
                'optionValue' => [L10nStorage::MONOLINGUAL => (string)$option['optionValue']],
            ],
            $options
        ));
    }

    public function validateVote(): void
    {
        $this->poll = $this->getSingleObject();
        $this->loadRelatedObject();

        if ($this->poll->isNil()) {
            throw new UserInputException('pollID');
        }

        if (!$this->poll->canVote()) {
            throw new PermissionDeniedException();
        }

        if (!isset($this->parameters['optionIDs']) || !\is_array($this->parameters['optionIDs'])) {
            throw new UserInputException('optionIDs');
        }

        $this->parameters['optionIDs'] = \array_map(
            \intval(...),
            $this->parameters['optionIDs']
        );

        if ($this->parameters['optionIDs'] === []) {
            throw new UserInputException('optionIDs');
        }

        if (\count($this->parameters['optionIDs']) > $this->poll->maxVotes) {
            throw new UserInputException('optionIDs', 'maxVotes');
        }

        $optionIDs = [];
        foreach ($this->poll->getOptions() as $option) {
            $optionIDs[] = $option->optionID;
        }

        foreach ($this->parameters['optionIDs'] as $optionID) {
            if (!\in_array($optionID, $optionIDs, true)) {
                throw new UserInputException('optionIDs', 'unknownOption');
            }
        }
    }

    /**
     * Executes a user's vote.
     *
     * @return array{
     *  changeableVote: int,
     *  totalVotes: int,
     *  totalVotesTooltip: string,
     *  template: string,
     * }
     */
    public function vote()
    {
        if (empty($this->poll)) {
            $this->poll = $this->getSingleObject();
        }

        (new VotePoll(
            $this->poll->getDecoratedObject(),
            WCF::getUser(),
            \array_values($this->parameters['optionIDs'])
        ))();

        // update poll object
        $polls = PollManager::getInstance()->getPolls([$this->poll->pollID]);
        $this->poll = new PollEditor($polls[$this->poll->pollID]);
        $this->loadRelatedObject();

        return [
            'changeableVote' => $this->poll->isChangeable,
            'totalVotes' => $this->poll->votes,
            'totalVotesTooltip' => WCF::getLanguage()->getDynamicVariable(
                'wcf.poll.totalVotes',
                ['poll' => $this->poll->getDecoratedObject()]
            ),
            'template' => WCF::getTPL()->render('wcf', 'pollResult', [
                'poll' => $this->poll->getDecoratedObject(),
            ]),
        ];
    }

    /**
     * @deprecated 6.3 No longer in use.
     */
    #[\Override]
    public function validateGetGroupedUserList()
    {
        $this->readInteger('pollID');

        // read poll
        $this->poll = new PollEditor(new Poll($this->parameters['pollID']));
        if ($this->poll->isNil()) {
            throw new UserInputException('pollID');
        } elseif (!$this->poll->canViewParticipants()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @throws UserInputException If not exactly one valid poll is given.
     * @throws PermissionDeniedException If the current user cannot see the result of the poll.
     * @since  5.5
     */
    public function validateGetResultTemplate(): void
    {
        $this->poll = $this->getSingleObject();
        $this->loadRelatedObject();

        if ($this->poll->isNil()) {
            throw new UserInputException('pollID');
        }

        if (!$this->poll->canSeeResult()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @return array{template: string}
     * @since  5.5
     */
    public function getResultTemplate(): array
    {
        return [
            'template' => WCF::getTPL()->render('wcf', 'pollResult', [
                'poll' => $this->poll->getDecoratedObject(),
            ]),
        ];
    }

    /**
     * @throws UserInputException If not exactly one valid poll is given.
     * @throws PermissionDeniedException If the current user cannot vote the poll.
     * @since  5.5
     */
    public function validateGetVoteTemplate(): void
    {
        $this->poll = $this->getSingleObject();
        $this->loadRelatedObject();

        if ($this->poll->isNil()) {
            throw new UserInputException('pollID');
        }

        if (!$this->poll->canVote()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @return array{template: string}
     * @since  5.5
     */
    public function getVoteTemplate(): array
    {
        return [
            'template' => WCF::getTPL()->render('wcf', 'pollVote', [
                'poll' => $this->poll,
            ]),
        ];
    }

    private function loadRelatedObject(): void
    {
        \assert($this->poll instanceof PollEditor);

        $relatedObject = PollManager::getInstance()->getRelatedObject($this->poll->getDecoratedObject());

        $this->poll->setRelatedObject($relatedObject);
    }

    /**
     * @deprecated 6.3 No longer in use.
     */
    #[\Override]
    public function getGroupedUserList()
    {
        // get options
        $options = [];
        foreach ($this->poll->getOptions(true) as $option) {
            $options[$option->optionID] = new GroupedUserList($option->getOptionValue(), 'wcf.poll.noVotes');
        }

        // get votes
        $sql = "SELECT  userID, optionID
                FROM    wcf1_poll_option_vote
                WHERE   pollID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->poll->pollID]);
        $voteData = $statement->fetchMap('optionID', 'userID', false);

        // assign user ids
        foreach ($voteData as $optionID => $userIDs) {
            $options[$optionID]->addUserIDs($userIDs);
        }

        // load user profiles
        GroupedUserList::loadUsers();

        return [
            'pageCount' => 1,
            'template' => WCF::getTPL()->render('wcf', 'groupedUserList', [
                'groupedUsers' => $options,
            ]),
        ];
    }

    /**
     * Copies a poll from one object id to another.
     *
     * @return array{pollID: ?int}
     */
    public function copy()
    {
        $sourceObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.poll',
            $this->parameters['sourceObjectType']
        );

        $pollList = new PollList();
        $pollList->getConditionBuilder()->add("poll.objectTypeID = ?", [$sourceObjectType->objectTypeID]);
        $pollList->getConditionBuilder()->add("poll.objectID = ?", [$this->parameters['sourceObjectID']]);
        $pollList->sqlLimit = 1;
        $pollList->readObjects();
        $poll = $pollList->getSingleObject();

        if ($poll === null) {
            return [
                'pollID' => null,
            ];
        }

        $newPoll = (new CopyPoll(
            $poll,
            $this->parameters['targetObjectType'],
            (int)$this->parameters['targetObjectID']
        ))();

        return [
            'pollID' => $newPoll->pollID,
        ];
    }
}
