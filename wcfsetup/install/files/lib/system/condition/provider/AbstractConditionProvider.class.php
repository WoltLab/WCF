<?php

namespace wcf\system\condition\provider;

use wcf\system\condition\type\IConditionType;
use wcf\system\form\builder\container\ConditionRowFormFieldContainer;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @template T of IConditionType
 */
abstract class AbstractConditionProvider
{
    /**
     * @var array<string, T>
     */
    protected array $conditionTypes = [];

    /**
     * Adds a condition type to this provider.
     *
     * @param T $conditionType
     */
    public function addCondition(IConditionType $conditionType): void
    {
        $this->conditionTypes[$conditionType->getIdentifier()] = $conditionType;
    }

    /**
     * Adds multiple condition types to this provider.
     *
     * @param T[] $conditionTypes
     */
    public function addConditions(array $conditionTypes): void
    {
        foreach ($conditionTypes as $conditionType) {
            $this->addCondition($conditionType);
        }
    }

    final public function getFieldId(string $containerId, string $identifier, int $index): string
    {
        return "{$containerId}_{$identifier}_{$index}";
    }

    final public function getConditionFormField(string $containerId, string $identifier, int $index, mixed $value = null): ConditionRowFormFieldContainer
    {
        $condition = $this->getConditionByIdentifier($identifier);
        if ($condition === null) {
            throw new \InvalidArgumentException("Condition type with identifier '{$identifier}' not found.");
        }

        $formField = $condition->getFormField($this->getFieldId($containerId, $identifier, $index));
        if ($value !== null) {
            $formField->value($value);
        }

        return ConditionRowFormFieldContainer::create("{$containerId}_{$identifier}_{$index}_container")
            ->containerId($containerId)
            ->conditionType($identifier)
            ->conditionIndex($index)
            ->label($condition->getLabel())
            ->appendChild($formField);
    }

    /**
     * Returns the condition type with the given identifier.
     *
     * @return T|null
     */
    public function getConditionByIdentifier(string $identifier): ?IConditionType
    {
        return $this->conditionTypes[$identifier] ?? null;
    }

    /**
     * Returns all condition types of this provider.
     *
     * @return array<string, T>
     */
    public function getConditionTypes(): array
    {
        return $this->conditionTypes;
    }
}
