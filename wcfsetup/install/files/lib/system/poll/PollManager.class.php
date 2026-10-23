<?php

namespace wcf\system\poll;

use wcf\command\poll\CreatePoll;
use wcf\command\poll\DeletePolls;
use wcf\command\poll\UpdatePoll;
use wcf\data\IPollContainer;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\poll\option\PollOptionList;
use wcf\data\poll\Poll;
use wcf\data\poll\PollBuilder;
use wcf\data\poll\PollList;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\l10n\L10nStorage;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Provides methods to create and manage polls.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PollManager extends SingletonFactory
{
    private const DEFAULT_POLL_DATA = [
        'endTime' => 0,
        'isChangeable' => 0,
        'isPublic' => 0,
        'maxVotes' => 1,
        'question' => '',
        'resultsRequireVote' => 0,
        'sortByVotes' => 0,
    ];

    /**
     * list of object types
     * @var ObjectType[]
     */
    protected $cache = [];

    /**
     * current object id
     * @var int
     */
    protected $objectID = 0;

    /**
     * current object type
     * @var string
     */
    protected $objectType = '';

    /**
     * poll object
     * @var ?Poll
     */
    protected $poll;

    /**
     * poll data
     * @var mixed[]
     */
    protected $pollData = self::DEFAULT_POLL_DATA;

    /**
     * poll id
     * @var ?int
     */
    protected $pollID = 0;

    /**
     * list of poll options
     * @var array<int, array{optionID: int, optionValue: string}>
     */
    protected $pollOptions = [];

    #[\Override]
    protected function init()
    {
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.poll');
        foreach ($objectTypes as $objectType) {
            $this->cache[$objectType->objectType] = $objectType;
        }
    }

    /**
     * Removes a list of polls by id.
     *
     * @param int[] $pollIDs
     * @return void
     */
    public function removePolls(array $pollIDs)
    {
        if ($pollIDs === []) {
            return;
        }

        $pollList = new PollList();
        $pollList->setObjectIDs($pollIDs);
        $pollList->readObjects();

        (new DeletePolls($pollList->getObjects()))();
    }

    /**
     * Sets object data.
     *
     * @return  bool
     * @throws  SystemException
     */
    public function setObject(string $objectType, int $objectID, ?int $pollID = 0)
    {
        if (!isset($this->cache[$objectType])) {
            throw new SystemException("Object type '" . $objectType . "' is unknown");
        }

        $this->objectID = \intval($objectID);
        $this->objectType = $objectType;
        $this->pollID = $pollID;

        // load poll
        if ($this->pollID !== null && $this->pollID !== 0) {
            $this->poll = new Poll($this->pollID);
            if ($this->poll->isNil()) {
                $this->poll = null;
                $this->pollID = 0;

                return false;
            }

            // populate poll data
            $this->pollData = [
                'endTime' => $this->poll->endTime,
                'isChangeable' => $this->poll->isChangeable,
                'isPublic' => $this->poll->isPublic,
                'maxVotes' => $this->poll->maxVotes,
                'question' => $this->poll->question,
                'resultsRequireVote' => $this->poll->resultsRequireVote,
                'sortByVotes' => $this->poll->sortByVotes,
            ];

            // load poll options
            foreach ($this->poll->getOptions() as $option) {
                $this->pollOptions[] = [
                    'optionID' => $option->optionID,
                    'optionValue' => $option->getOptionValue(),
                ];
            }
        }

        return true;
    }

    /**
     * Reads form parameters for polls.
     *
     * @param array<string, mixed> $postData optional post data to be used instead of $_POST
     * @return void
     */
    public function readFormParameters(array $postData = [])
    {
        if ($postData === []) {
            $postData = &$_POST;
        }

        // reset poll data and options prior to reading form input
        $this->pollData = self::DEFAULT_POLL_DATA;
        $this->pollOptions = [];

        // poll data
        if (isset($postData['pollEndTime'])) {
            $d = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $postData['pollEndTime']);
            $this->pollData['endTime'] = ($d !== false) ? $d->getTimestamp() : 0;
        }

        if (isset($postData['pollMaxVotes'])) {
            $this->pollData['maxVotes'] = \max(\intval($postData['pollMaxVotes']), 1); // force a minimum of 1
        }
        if (isset($postData['pollQuestion'])) {
            $this->pollData['question'] = StringUtil::trim($postData['pollQuestion']);
        }

        // boolean values
        $this->pollData['isChangeable'] = isset($postData['pollIsChangeable']) ? 1 : 0;
        $this->pollData['resultsRequireVote'] = isset($postData['pollResultsRequireVote']) ? 1 : 0;
        $this->pollData['sortByVotes'] = isset($postData['pollSortByVotes']) ? 1 : 0;

        if ($this->poll === null) {
            $this->pollData['isPublic'] = (isset($postData['pollIsPublic']) && $this->canStartPublicPoll()) ? 1 : 0;
        } else {
            // visibility cannot be changed after creation
            $this->pollData['isPublic'] = $this->poll->isPublic;
        }

        // poll options
        if (isset($postData['pollOptions']) && \is_array($postData['pollOptions'])) {
            foreach ($postData['pollOptions'] as $showOrder => $value) {
                [$optionID, $optionValue] = \explode('_', $value, 2);
                $this->pollOptions[$showOrder] = [
                    'optionID' => \intval($optionID),
                    'optionValue' => StringUtil::trim($optionValue),
                ];
            }
        }
    }

    /**
     * Sets the raw poll data that has already been validated.
     * Intended for use in combination with the `WysiwygPollFormContainer`.
     *
     * @param array<string, mixed> $pollData
     * @since 6.2
     */
    public function setPollData(array $pollData): void
    {
        $this->pollOptions = $pollData['options'];
        unset($pollData['options']);

        $this->pollData = $pollData;
    }

    /**
     * Validates poll parameters.
     *
     * @return void
     */
    public function validate()
    {
        $count = \count($this->pollOptions);

        // if no question and no options are given, ignore poll completely
        if (empty($this->pollData['question']) && $count === 0) {
            return;
        }

        // no question given
        if (empty($this->pollData['question'])) {
            throw new UserInputException('pollQuestion');
        }

        if (!empty($this->pollData['endTime']) && $this->pollData['endTime'] <= \TIME_NOW) {
            if ($this->poll === null || $this->poll->endTime >= \TIME_NOW) {
                // end time is in the past
                throw new UserInputException('pollEndTime', 'invalid');
            }
        }

        // no options given
        if ($count === 0) {
            throw new UserInputException('pollOptions');
        }

        // too many options provided, discard superfluous options
        if ($count > \POLL_MAX_OPTIONS) {
            $this->pollOptions = \array_slice($this->pollOptions, 0, \POLL_MAX_OPTIONS);
        }

        // less options available than allowed
        if ($count < $this->pollData['maxVotes']) {
            throw new UserInputException('pollMaxVotes', 'invalid');
        }
    }

    /**
     * Handles poll creation, modification and deletion. Returns poll id or zero
     * if poll was deleted or nothing was created.
     *
     * @return  int
     * @throws  SystemException
     */
    public function save(?int $objectID = null)
    {
        if ($objectID !== null) {
            $this->objectID = \intval($objectID);
        }

        // create a new poll
        if ($this->poll === null) {
            // no poll should be created
            if (empty($this->pollData['question'])) {
                return 0;
            }

            // validate if object type is given
            if (empty($this->objectType)) {
                throw new SystemException("Could not create poll, missing object type");
            }

            $builder = PollBuilder::forCreate()
                ->setContainer($this->objectType, $this->objectID)
                ->setTime(\TIME_NOW)
                ->setOptions($this->getOptionsForBuilder());
            $this->applyPollData($builder);

            $this->poll = (new CreatePoll($builder))();
        } else {
            // remove poll
            if (empty($this->pollData['question'])) {
                (new DeletePolls([$this->poll]))();
                $this->poll = null;

                return 0;
            } else {
                // update existing poll
                $builder = PollBuilder::forUpdate($this->poll)
                    ->setOptions($this->getOptionsForBuilder());
                $this->applyPollData($builder);

                $this->poll = (new UpdatePoll($builder))();
            }
        }

        return $this->poll->pollID;
    }

    private function applyPollData(PollBuilder $builder): void
    {
        $builder
            ->setQuestion([L10nStorage::MONOLINGUAL => (string)$this->pollData['question']])
            ->setEndTime((int)$this->pollData['endTime'])
            ->setIsChangeable((bool)$this->pollData['isChangeable'])
            ->setIsPublic((bool)$this->pollData['isPublic'])
            ->setResultsRequireVote((bool)$this->pollData['resultsRequireVote'])
            ->setSortByVotes((bool)$this->pollData['sortByVotes'])
            ->setMaxVotes((int)$this->pollData['maxVotes']);
    }

    /**
     * @return list<array{optionID: int, optionValue: array<int, string>}>
     */
    private function getOptionsForBuilder(): array
    {
        $options = $this->pollOptions;
        \ksort($options);

        return \array_values(\array_map(
            static fn(array $option) => [
                'optionID' => $option['optionID'],
                'optionValue' => [L10nStorage::MONOLINGUAL => $option['optionValue']],
            ],
            $options
        ));
    }

    /**
     * Saves the poll for the given poll container using the given poll data and returns the id
     * of the relevant poll or `null` if the poll container has no poll after the update.
     *
     * This method either creates a new poll or updates or deletes an existing poll.
     *
     * @param IPollContainer $pollContainer object the poll belongs to
     * @param mixed[] $pollData poll data
     * @return  null|int                    id of the poll
     * @since   5.2
     */
    public function savePoll(IPollContainer $pollContainer, array $pollData)
    {
        // backup internal data
        $backupData = [
            'objectID' => $this->objectID,
            'poll' => $this->poll,
            'pollData' => $this->pollData,
            'pollOptions' => $this->pollOptions,
        ];

        if ($pollContainer->getPollID() !== null) {
            $this->poll = new Poll($pollContainer->getPollID());
        }

        $this->pollOptions = $pollData['options'];
        unset($pollData['options']);
        $this->pollData = $pollData;

        $pollID = $this->save($pollContainer->getObjectID());

        // restore internal data
        $this->objectID = $backupData['objectID'];
        $this->poll = $backupData['poll'];
        $this->pollData = $backupData['pollData'];
        $this->pollOptions = $backupData['pollOptions'];

        return $pollID ?: null;
    }

    /**
     * Assigns variables for poll management or display.
     *
     * @return void
     */
    public function assignVariables()
    {
        $variables = [
            '__showPoll' => true,
            'pollID' => $this->poll === null ? 0 : $this->poll->pollID,
            'pollOptions' => $this->pollOptions,
        ];
        foreach ($this->pollData as $key => $value) {
            if ($key === 'endTime') {
                if (empty($value)) {
                    $value = '';
                }
            }

            $key = 'poll' . \ucfirst($key);
            $variables[$key] = $value;
        }

        WCF::getTPL()->assign($variables);
    }

    /**
     * Returns true if current user can start a public poll.
     *
     * @return  bool
     */
    public function canStartPublicPoll()
    {
        $handler = $this->getHandler(null, $this->objectType);

        return $handler->canStartPublicPoll();
    }

    /**
     * Returns a list of polls including options and vote state for current user.
     *
     * @param int[] $pollIDs
     * @return  Poll[]
     */
    public function getPolls(array $pollIDs)
    {
        $pollList = new PollList();
        $pollList->setObjectIDs($pollIDs);
        $pollList->readObjects();
        $polls = $pollList->getObjects();

        // invalid poll ids
        if ($polls === []) {
            return [];
        }

        // fetch options for every poll
        $optionList = $this->getPollOptions($pollIDs);

        // assign options to poll
        foreach ($optionList as $option) {
            $polls[$option->pollID]->addOption($option);
        }

        return $polls;
    }

    /**
     * Returns a list of poll options with vote state for current user.
     *
     * @param int[] $pollIDs
     * @return  PollOptionList
     */
    public function getPollOptions(array $pollIDs)
    {
        $optionList = new PollOptionList();
        $optionList->getConditionBuilder()->add("poll_option.pollID IN (?)", [$pollIDs]);

        // check for user votes
        if (!WCF::getUser()->isGuest()) {
            $optionList->sqlSelects = "CASE WHEN poll_option_vote.optionID IS NULL THEN '0' ELSE '1' END AS voted";
            $optionList->sqlJoins = "
                LEFT JOIN   wcf1_poll_option_vote poll_option_vote
                ON          poll_option_vote.optionID = poll_option.optionID
                        AND poll_option_vote.userID = " . WCF::getUser()->userID;
        } else {
            $optionList->sqlSelects = "'0' AS voted";
        }

        $optionList->readObjects();

        return $optionList;
    }

    /**
     * Returns related object for given poll object.
     *
     * @param Poll $poll
     * @return  \wcf\data\IPollObject|null
     */
    public function getRelatedObject(Poll $poll)
    {
        if (!$poll->isStandalone() && $poll->objectID !== 0) {
            return $this->getHandler($poll->objectTypeID)->getRelatedObject($poll);
        }

        return null;
    }

    /**
     * Assigns the related object to the given poll, polls without a poll
     * container (standalone polls) have no related object.
     *
     * @since 6.3
     */
    public function loadRelatedObject(Poll $poll): void
    {
        $relatedObject = $this->getRelatedObject($poll);
        if ($relatedObject !== null) {
            $poll->setRelatedObject($relatedObject);
        }
    }

    /**
     * Returns the handler object for given object type. Returns false if object type (id)
     * is not found, or null if no handler is assigned.
     *
     * @return  IPollHandler
     * @throws  SystemException
     */
    protected function getHandler(?int $objectTypeID, string $objectType = '')
    {
        if ($objectTypeID !== null) {
            foreach ($this->cache as $objectTypeObj) {
                if ($objectTypeObj->objectTypeID === $objectTypeID) {
                    $objectType = $objectTypeObj->objectType;
                    break;
                }
            }
        }

        if (!isset($this->cache[$objectType])) {
            throw new SystemException("Object type '" . $objectType . "' (id " . $objectTypeID . ") is not valid for object type definition 'com.woltlab.wcf.poll'");
        }

        if ($this->cache[$objectType]->className === '') {
            throw new SystemException("Object type '" . $objectType . "' does not provide a processor class name");
        }

        // validates against object type's class
        $className = $this->cache[$objectType]->className;
        if (!\is_subclass_of($className, IPollHandler::class)) {
            throw new ImplementationException($className, IPollHandler::class);
        } elseif (!\is_subclass_of($className, SingletonFactory::class)) {
            throw new ParentClassException($className, SingletonFactory::class);
        }

        return \call_user_func([$className, 'getInstance']);
    }
}
