<?php

namespace wcf\data\poll;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\poll\option\PollOption;
use wcf\data\poll\option\PollOptionBuilder;
use wcf\data\poll\option\PollOptionList;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting polls including their options.
 *
 * The localized question and the localized option values are stored in the
 * `wcf1_poll_l10n` and `wcf1_poll_option_l10n` tables (see `L10nStorage`). The
 * values are normalized to the mode of the poll: a monolingual poll stores a
 * single value, a multilingual poll stores a value per language.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Poll>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 * @phpstan-type AclValues array{allowAll: bool, user: list<int>, group: list<int>}
 * @phpstan-type PollOptionData array{optionID?: int, optionValue: L10nValue}
 */
final class PollBuilder extends DatabaseObjectBuilder
{
    private const ACL_OBJECT_TYPE = 'com.woltlab.wcf.poll';

    /**
     * @var L10nValue
     */
    private array $question;

    /**
     * @var list<PollOptionData>
     */
    private array $options;

    /**
     * @var AclValues
     */
    private array $aclValues;

    private bool $recalculateVotes = false;

    /**
     * Assigns the poll to the poll container of the given object type, see the
     * object type definition `com.woltlab.wcf.poll`.
     */
    public function setContainer(string $objectType, int $objectID): static
    {
        $objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.poll', $objectType);
        if ($objectTypeObj === null) {
            throw new \InvalidArgumentException("Unknown poll object type '{$objectType}'.");
        }

        $this->properties['objectTypeID'] = $objectTypeObj->objectTypeID;
        $this->properties['objectID'] = $objectID;

        return $this;
    }

    /**
     * Sets the id of the poll container object, the object type must not change.
     */
    public function setObjectID(int $objectID): static
    {
        $this->properties['objectID'] = $objectID;

        return $this;
    }

    /**
     * Sets the user who created the standalone poll.
     */
    public function setUser(?int $userID, string $username): static
    {
        $this->properties['userID'] = $userID;
        $this->properties['username'] = $username;

        return $this;
    }

    /**
     * Sets whether the question and the options are stored per language.
     */
    public function setMultilingual(bool $isMultilingual): static
    {
        $this->properties['isMultilingual'] = (int)$isMultilingual;

        return $this;
    }

    /**
     * @param L10nValue $question
     */
    public function setQuestion(array $question): static
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Replaces the options of the poll. Options are matched by their `optionID`
     * on update, options without an id are created, existing options that are
     * missing are deleted. The order of the list determines the show order.
     *
     * @param list<PollOptionData> $options
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the time at which the poll closes, `0` for polls without an end.
     */
    public function setEndTime(int $endTime): static
    {
        $this->properties['endTime'] = $endTime;

        return $this;
    }

    public function setIsChangeable(bool $isChangeable): static
    {
        $this->properties['isChangeable'] = (int)$isChangeable;

        return $this;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->properties['isPublic'] = (int)$isPublic;

        return $this;
    }

    public function setSortByVotes(bool $sortByVotes): static
    {
        $this->properties['sortByVotes'] = (int)$sortByVotes;

        return $this;
    }

    public function setResultsRequireVote(bool $resultsRequireVote): static
    {
        $this->properties['resultsRequireVote'] = (int)$resultsRequireVote;

        return $this;
    }

    public function setMaxVotes(int $maxVotes): static
    {
        $this->properties['maxVotes'] = \max($maxVotes, 1);

        return $this;
    }

    /**
     * Sets the number of participants, intended for imported polls.
     */
    public function setVotes(int $votes): static
    {
        $this->properties['votes'] = $votes;

        return $this;
    }

    /**
     * Sets the users and groups that can access the standalone poll, see `SimpleAclHandler`.
     *
     * @param AclValues $aclValues
     */
    public function setAclValues(array $aclValues): static
    {
        $this->aclValues = $aclValues;

        return $this;
    }

    /**
     * Recalculates the number of votes of the poll and its options from the
     * stored votes.
     */
    public function recalculateVotes(): static
    {
        $this->recalculateVotes = true;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['time'];
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        if (!isset($this->question)) {
            throw new \BadMethodCallException("Missing value for the question.");
        }
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        $this->saveQuestion($object);
        $this->saveOptions($object);
        $this->saveAclValues($object);

        if ($this->recalculateVotes) {
            self::calculateVotes($object->pollID);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        $modeChanged = \array_key_exists('isMultilingual', $this->properties)
            && $this->getObject()->isMultilingual !== $object->isMultilingual;

        if (isset($this->question) || $modeChanged) {
            $this->saveQuestion($object);
        }

        if (isset($this->options) || $modeChanged) {
            $this->saveOptions($object);
        }

        $this->saveAclValues($object);

        if ($this->recalculateVotes || isset($this->options)) {
            self::calculateVotes($object->pollID);
        }
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        $objectTypeID = SimpleAclHandler::getInstance()->getObjectTypeID(self::ACL_OBJECT_TYPE);

        foreach (['wcf1_acl_simple_to_user', 'wcf1_acl_simple_to_group'] as $tableName) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('objectTypeID = ?', [$objectTypeID]);
            $conditionBuilder->add('objectID IN (?)', [$objectIDs]);

            $sql = "DELETE FROM {$tableName}
                    {$conditionBuilder}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());
        }

        SimpleAclResolver::getInstance()->resetCache(self::ACL_OBJECT_TYPE);
    }

    private function saveQuestion(Poll $poll): void
    {
        $definition = Poll::getL10nDefinition();

        $question = $this->question ?? null;
        if ($question === null && $this->isUpdate()) {
            $question = $this->getObject()->getL10nValues('question');
        }

        (new L10nStorage($definition))->setValues($poll->pollID, [
            'question' => \array_map(
                static fn(string $value) => $definition->truncateValue('question', $value),
                self::normalizeL10nValue($question ?? [], $poll->isMultilingual === 1)
            ),
        ]);
    }

    private function saveOptions(Poll $poll): void
    {
        $existingOptions = [];
        if ($this->isUpdate()) {
            $optionList = new PollOptionList();
            $optionList->getConditionBuilder()->add("poll_option.pollID = ?", [$poll->pollID]);
            $optionList->readObjects();
            $existingOptions = $optionList->getObjects();
        }

        $options = $this->options ?? null;
        if ($options === null) {
            // Only the mode of the poll has changed, the values must be normalized.
            $options = \array_map(
                static fn(PollOption $option) => [
                    'optionID' => $option->optionID,
                    'optionValue' => $option->getL10nValues('optionValue'),
                ],
                \array_values($existingOptions)
            );
            \usort(
                $options,
                static fn(array $a, array $b) => $existingOptions[$a['optionID']]->showOrder <=> $existingOptions[$b['optionID']]->showOrder
            );
        }

        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            foreach ($options as $showOrder => $option) {
                $optionValue = self::normalizeL10nValue($option['optionValue'], $poll->isMultilingual === 1);
                $optionID = $option['optionID'] ?? 0;

                if (isset($existingOptions[$optionID])) {
                    PollOptionBuilder::forUpdate($existingOptions[$optionID])
                        ->setOptionValue($optionValue)
                        ->setShowOrder($showOrder)
                        ->update();

                    unset($existingOptions[$optionID]);
                } else {
                    PollOptionBuilder::forCreate()
                        ->setPollID($poll->pollID)
                        ->setOptionValue($optionValue)
                        ->setShowOrder($showOrder)
                        ->create();
                }
            }

            if ($existingOptions !== []) {
                PollOptionBuilder::deleteAll(\array_keys($existingOptions));
            }

            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }
    }

    private function saveAclValues(Poll $poll): void
    {
        if (!isset($this->aclValues)) {
            return;
        }

        SimpleAclHandler::getInstance()->setValues(self::ACL_OBJECT_TYPE, $poll->pollID, $this->aclValues);
    }

    /**
     * Converts the given values to match the mode of the poll: a monolingual
     * poll stores a single value, a multilingual poll stores a value per
     * language.
     *
     * @param L10nValue $values
     * @return L10nValue
     */
    private static function normalizeL10nValue(array $values, bool $isMultilingual): array
    {
        if (!$isMultilingual) {
            if (isset($values[L10nStorage::MONOLINGUAL])) {
                return [L10nStorage::MONOLINGUAL => $values[L10nStorage::MONOLINGUAL]];
            }

            return [L10nStorage::MONOLINGUAL => L10nStorage::resolveValue($values)];
        }

        if ($values !== [] && !isset($values[L10nStorage::MONOLINGUAL])) {
            return $values;
        }

        $value = $values[L10nStorage::MONOLINGUAL] ?? '';
        $normalizedValues = [];
        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            $normalizedValues[$language->languageID] = $value;
        }

        return $normalizedValues;
    }

    /**
     * Recalculates the number of votes of the given poll and its options.
     */
    private static function calculateVotes(int $pollID): void
    {
        $sql = "UPDATE  wcf1_poll_option poll_option
                SET     poll_option.votes = (
                            SELECT  COUNT(*)
                            FROM    wcf1_poll_option_vote
                            WHERE   optionID = poll_option.optionID
                        )
                WHERE   poll_option.pollID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$pollID]);

        $sql = "UPDATE  wcf1_poll poll
                SET     poll.votes = (
                            SELECT  COUNT(DISTINCT userID)
                            FROM    wcf1_poll_option_vote
                            WHERE   pollID = poll.pollID
                        )
                WHERE   poll.pollID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$pollID]);
    }
}
