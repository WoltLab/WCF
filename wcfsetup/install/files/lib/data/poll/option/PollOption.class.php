<?php

namespace wcf\data\poll\option;

use wcf\data\CollectionDatabaseObject;
use wcf\data\poll\Poll;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;

/**
 * Represents a poll option.
 *
 * The localized value is stored in the `wcf1_poll_option_l10n` table.
 *
 * @author  Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $optionID       unique id of the poll option
 * @property-read   int     $pollID         id of the poll the option belongs to
 * @property-read   string  $optionValue    localized text of the poll option
 * @property-read   int     $votes          number of votes for the poll option
 * @property-read   int     $showOrder      position of the poll option in relation to the other options of the poll
 *
 * @extends CollectionDatabaseObject<PollOptionCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class PollOption extends CollectionDatabaseObject implements \Stringable
{
    /**
     * names of the localized columns that are exposed as properties
     */
    private const L10N_COLUMNS = ['optionValue'];

    /**
     * true, if option was selected by current user
     * @var bool
     */
    public $selected = false;

    /**
     * Returns relative amount of votes for this option.
     *
     * @param Poll $poll
     * @return  int
     */
    public function getRelativeVotes(Poll $poll)
    {
        if ($poll->votes !== 0) {
            return (int)\round(($this->votes / $poll->votes) * 100);
        }

        return 0;
    }

    /**
     * @since 6.3
     */
    public function getOptionValue(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'optionValue');
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

    #[\Override]
    public function __get(string $name)
    {
        if (\in_array($name, self::L10N_COLUMNS, true)) {
            return $this->getOptionValue();
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
    public function __toString(): string
    {
        return $this->getOptionValue();
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_poll_option',
            'wcf1_poll_option_l10n',
            'optionID',
            ['optionValue'],
            maximumLengths: ['optionValue' => 255],
        );
    }
}
