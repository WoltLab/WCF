<?php

namespace wcf\data\poll\option;

use wcf\data\DatabaseObjectBuilder;
use wcf\system\l10n\L10nStorage;

/**
 * Builder for creating, updating and deleting poll options. Poll options are
 * usually managed through the `PollBuilder`.
 *
 * The localized value is stored in the `wcf1_poll_option_l10n` table (see
 * `L10nStorage`).
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<PollOption>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class PollOptionBuilder extends DatabaseObjectBuilder
{
    /**
     * @var L10nValue
     */
    private array $optionValue;

    public function setPollID(int $pollID): static
    {
        $this->properties['pollID'] = $pollID;

        return $this;
    }

    /**
     * @param L10nValue $optionValue
     */
    public function setOptionValue(array $optionValue): static
    {
        $this->optionValue = $optionValue;

        return $this;
    }

    public function setShowOrder(int $showOrder): static
    {
        $this->properties['showOrder'] = $showOrder;

        return $this;
    }

    public function setVotes(int $votes): static
    {
        $this->properties['votes'] = $votes;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['pollID'];
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        if (!isset($this->optionValue)) {
            throw new \BadMethodCallException("Missing value for the option value.");
        }
    }

    #[\Override]
    protected function afterCreate(): void
    {
        $this->saveL10nValues($this->getObject());
    }

    #[\Override]
    protected function afterUpdate(): void
    {
        if (isset($this->optionValue)) {
            $this->saveL10nValues($this->getObject());
        }
    }

    private function saveL10nValues(PollOption $option): void
    {
        $definition = PollOption::getL10nDefinition();

        (new L10nStorage($definition))->setValues($option->optionID, [
            'optionValue' => \array_map(
                static fn(string $value) => $definition->truncateValue('optionValue', $value),
                $this->optionValue
            ),
        ]);
    }
}
