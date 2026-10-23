<?php

namespace wcf\data\poll;

use wcf\data\CollectionDatabaseObject;
use wcf\data\IPollObject;
use wcf\data\ITitledObject;
use wcf\data\poll\option\PollOption;
use wcf\data\user\UserProfile;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Represents a poll.
 *
 * A poll either belongs to a poll container (`objectTypeID` references an object
 * type of the definition `com.woltlab.wcf.poll`) or it is a standalone poll
 * (`objectTypeID = NULL`) that is owned by a user and embedded into messages
 * via the `[poll]` bbcode.
 *
 * The localized question is stored in the `wcf1_poll_l10n` table. A poll is
 * either monolingual (`isMultilingual = 0`), storing a single value, or it is
 * multilingual, storing a value per language.
 *
 * @author  Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $pollID             unique id of the poll
 * @property-read   ?int    $objectTypeID       id of the `com.woltlab.wcf.poll` object type or `null` for standalone polls
 * @property-read   int     $objectID           id of the poll container object the poll belongs to or `0` for standalone polls
 * @property-read   ?int    $userID             id of the user who created the standalone poll or `null` if the user does not exist anymore
 * @property-read   string  $username           name of the user who created the standalone poll
 * @property-read   0|1     $isMultilingual     is `1` if the question and the options are available in multiple languages, otherwise `0`
 * @property-read   string  $question           localized question of the poll
 * @property-read   int     $time               timestamp at which the poll has been created
 * @property-read   int     $endTime            timestamp at which the poll has been/will be closed
 * @property-read   0|1     $isChangeable       is `1` if participants can change their vote, otherwise `0`
 * @property-read   0|1     $isPublic           is `1` if the result of the poll is public, otherwise `0`
 * @property-read   0|1     $sortByVotes        is `1` if the results will be sorted by votes, otherwise `0`
 * @property-read   0|1     $resultsRequireVote is `1` if a user has to have voted to see the results, otherwise `0`
 * @property-read   0|1     $resultsRequireEnd  is `1` if the results are hidden from everyone until the poll has ended, otherwise `0`
 * @property-read   int     $minVotes           minimum number of options the user has to select
 * @property-read   int     $maxVotes           maximum number of options the user can select
 * @property-read   int     $votes              number of votes in the poll
 *
 * @extends CollectionDatabaseObject<PollCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class Poll extends CollectionDatabaseObject implements ITitledObject, \Stringable
{
    /**
     * names of the localized columns that are exposed as properties
     */
    private const L10N_COLUMNS = ['question'];

    /**
     * participation status
     * @var bool
     */
    protected $isParticipant = false;

    /**
     * list of poll options
     * @var PollOption[]
     */
    protected $options = [];

    /**
     * related object
     * @var ?IPollObject
     */
    protected $relatedObject;

    /**
     * Adds an option to current poll.
     *
     * @return void
     */
    public function addOption(PollOption $option)
    {
        if ($option->pollID === $this->pollID) {
            $this->options[$option->optionID] = $option;

            // @phpstan-ignore property.notFound
            if ($option->voted) {
                $this->isParticipant = true;
            }
        }
    }

    /**
     * Returns a list of poll options.
     *
     * @return PollOption[]
     */
    public function getOptions(bool $isResultDisplay = false)
    {
        $this->loadOptions();

        if ($isResultDisplay && $this->sortByVotes !== 0) {
            \uasort($this->options, static function ($a, $b) {
                if ($a->votes === $b->votes) {
                    return 0;
                }

                return ($a->votes > $b->votes) ? -1 : 1;
            });
        } else {
            // order options by show order
            \uasort($this->options, static function ($a, $b) {
                return ($a->showOrder < $b->showOrder) ? -1 : 1;
            });
        }

        return $this->options;
    }

    /**
     * Returns true if current user is a participant.
     *
     * @return bool
     */
    public function isParticipant()
    {
        $this->loadOptions();

        return $this->isParticipant;
    }

    /**
     * Loads associated options.
     *
     * @return void
     */
    protected function loadOptions()
    {
        if ($this->options !== []) {
            return;
        }

        foreach ($this->getCollection()->getOptions($this) as $option) {
            $this->addOption($option);
        }
    }

    /**
     * Returns the number of options the user has to select at least, which
     * never exceeds the maximum number of votes or the number of options.
     *
     * @since 6.3
     */
    public function getRequiredVotes(): int
    {
        return \max(1, \min($this->minVotes, $this->maxVotes, \count($this->getOptions())));
    }

    /**
     * Returns true if poll is already finished.
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->endTime !== 0 && $this->endTime <= \TIME_NOW;
    }

    /**
     * Returns true if this poll is not bound to a poll container but embedded
     * into messages via bbcode.
     *
     * @since 6.3
     */
    public function isStandalone(): bool
    {
        return $this->objectTypeID === null;
    }

    /**
     * Returns true if the active user can edit and delete this standalone poll.
     *
     * @since 6.3
     */
    public function canManage(): bool
    {
        if (!$this->isStandalone() || \MODULE_POLL === 0) {
            return false;
        }

        if (WCF::getSession()->hasPermission('mod.poll.canManagePoll')) {
            return true;
        }

        return $this->userID !== null
            && $this->userID === WCF::getUser()->userID
            && WCF::getSession()->hasPermission('user.poll.canCreatePoll');
    }

    /**
     * Returns true if the active user can access this poll. The access to polls
     * of a poll container is controlled by the container itself.
     *
     * @since 6.3
     */
    public function isAccessible(): bool
    {
        if (!$this->isStandalone()) {
            return true;
        }

        if (\MODULE_POLL === 0) {
            return false;
        }

        if ($this->canManage()) {
            return true;
        }

        return SimpleAclResolver::getInstance()->canAccess('com.woltlab.wcf.poll', $this->pollID);
    }

    /**
     * Returns true if current user can vote.
     *
     * @return bool
     */
    public function canVote()
    {
        // guest voting is not possible
        if (WCF::getUser()->isGuest()) {
            return false;
        } elseif ($this->isFinished()) {
            return false;
        } elseif ($this->isParticipant() && $this->isChangeable === 0) {
            return false;
        }

        if ($this->isStandalone()) {
            return $this->isAccessible() && WCF::getSession()->hasPermission('user.poll.canVotePoll');
        }

        if ($this->objectID !== 0) {
            // related object required but not given, deny vote ability
            if ($this->relatedObject === null) {
                return false;
            }

            // validate permissions
            if (!$this->relatedObject->canVote()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if current user can see the result.
     *
     * @return bool
     */
    public function canSeeResult()
    {
        if (!$this->isAccessible()) {
            return false;
        }

        if ($this->resultsRequireEnd === 1) {
            return $this->isFinished();
        }

        if ($this->isFinished() || $this->isParticipant() || $this->resultsRequireVote === 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if current user can view the participant list.
     *
     * @return bool
     */
    public function canViewParticipants()
    {
        if ($this->canSeeResult() && $this->isPublic !== 0) {
            return true;
        }

        return false;
    }

    /**
     * Sets related object for this poll.
     *
     * @return void
     */
    public function setRelatedObject(IPollObject $object)
    {
        $this->relatedObject = $object;
    }

    /**
     * Returns related object.
     *
     * @return IPollObject
     */
    public function getRelatedObject()
    {
        return $this->relatedObject;
    }

    /**
     * @since 6.3
     */
    public function getQuestion(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'question');
    }

    /**
     * Returns the localized values of the given column as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if (!\in_array($columnName, self::L10N_COLUMNS, true)) {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * Returns the profile of the user who created the standalone poll.
     *
     * @since 6.3
     */
    public function getUserProfile(): UserProfile
    {
        return $this->getCollection()->getUserProfile($this);
    }

    #[\Override]
    public function __get(string $name)
    {
        if (\in_array($name, self::L10N_COLUMNS, true)) {
            return $this->getCollection()->getResolvedL10nValue($this, $name);
        }

        return parent::__get($name);
    }

    #[\Override]
    public function __isset(string $name)
    {
        if (\in_array($name, self::L10N_COLUMNS, true)) {
            return true;
        }

        return parent::__isset($name);
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getQuestion();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getQuestion();
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_poll',
            'wcf1_poll_l10n',
            'pollID',
            ['question'],
            maximumLengths: ['question' => 255],
        );
    }
}
