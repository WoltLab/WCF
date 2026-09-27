<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\system\form\builder\field\AbstractFormField;

/**
 * Represents a single condition that can be configured for an object filter
 * and tested against individual objects.
 *
 * The configured value is stored in its serialized form and converted back
 * into its native type before being evaluated.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject
 * @phpstan-template-contravariant TDatabaseObject of DatabaseObject
 * @template TValueType of mixed
 */
interface IObjectFilter
{
    /**
     * Returns the unique identifier of this filter, e.g. `com.woltlab.wcf.username`.
     */
    public function getIdentifier(): string;

    /**
     * Returns the title of this filter that is shown when selecting a filter.
     */
    public function getTitle(): string;

    /**
     * Returns the form field that the user interacts with when entering a value.
     */
    public function getFormField(): AbstractFormField;

    /**
     * Returns true if this filter can be selected when configuring a filter,
     * e.g. false if there are no objects to choose from. Stored values of an
     * unavailable filter are still evaluated.
     */
    public function isAvailable(): bool;

    /**
     * Returns true if this filter can be configured more than once for the
     * same object. Filters whose repeated use is redundant or contradictory,
     * e.g. a yes/no choice, return false.
     */
    public function isRepeatable(): bool;

    /**
     * Converts the given value into its string representation for storage.
     *
     * When a filter is configured, the given value is the value of the form
     * field returned by `getFormField()`, which may differ from `TValueType`.
     *
     * @param TValueType $value
     */
    public function serializeValue(mixed $value): string;

    /**
     * Restores the value from its string representation created by `serializeValue()`.
     *
     * @return TValueType
     */
    public function unserializeValue(string $serializedValue): mixed;

    /**
     * Converts the given value into the value of the form field returned by
     * `getFormField()`, used to prefill the form field when editing the filter.
     *
     * @param TValueType $value
     */
    public function toFormFieldValue(mixed $value): mixed;

    /**
     * Returns a human-readable summary of the configured value,
     * e.g. "In user group <strong>%s</strong>".
     *
     * @param TValueType $value
     */
    public function summarizeValue(mixed $value): string;

    /**
     * Returns whether the given object matches the configured value.
     *
     * @param TDatabaseObject $object
     * @param TValueType $configuredValue
     */
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool;
}
