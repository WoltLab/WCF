<?php

namespace wcf\system\object\filter;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\AbstractFormField;

/**
 * @template-covariant TValueType of mixed
 */
interface IObjectFilter
{
    // `com.woltlab.wcf.username`
    public function getIdentifier(): string;

    public function getTitle(): string;

    /**
     * Returns the form field that the user interacts with when entering a value.
     *
     * @param array<string, mixed> $configuration
     */
    public function getFormField(): AbstractFormField;

    // -> DB
    public function serializeValue(mixed $value): string;

    /**
     * @return TValueType
     */
    public function unserializeValue(string $serializedValue): mixed;

    // "In user group <strong>%s</strong>"
    /**
     * @param TValueType $value
     */
    public function summarizeValue(mixed $value): string;

    /**
     * @param TValueType $value
     */
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void;

    /**
     * @param TValueType $configuredValue
     * @param mixed $value
     */
    public function testValue(mixed $configuredValue, mixed $value): bool;
}
