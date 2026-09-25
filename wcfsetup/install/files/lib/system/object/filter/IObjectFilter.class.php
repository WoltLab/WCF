<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\system\form\builder\field\AbstractFormField;

/**
 * @template TDatabaseObject of DatabaseObject
 * @template TValueType of mixed
 */
interface IObjectFilter
{
    // `com.woltlab.wcf.username`
    public function getIdentifier(): string;

    public function getTitle(): string;

    /**
     * Returns the form field that the user interacts with when entering a value.
     */
    public function getFormField(): AbstractFormField;

    /**
     * @param TValueType $value
     */
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
     * @param TDatabaseObject $object
     * @param TValueType $configuredValue
     */
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool;
}
