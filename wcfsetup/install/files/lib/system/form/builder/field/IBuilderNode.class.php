<?php

namespace wcf\system\form\builder\field;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\IStorableObject;

/**
 * Represents an actual form field storing a value.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
interface IBuilderNode
{
    /**
     * Sets a callback that transfers this field's save value into a
     * `DatabaseObjectBuilder` instance and returns this field.
     *
     * The callback is invoked by `DatabaseObjectBuilderFormDocument` when the
     * builder is populated from the form's fields, for example:
     *
     *     $field->saveValueCallback(
     *         static function (DatabaseObjectBuilder $builder, IFormField $formField) {
     *             return $builder->setName($formField->getSaveValue());
     *         }
     *     )
     *
     * The builder type is a template parameter so that the callback may narrow
     * it to a concrete `DatabaseObjectBuilder` implementation (e.g. `TagBuilder`)
     * without triggering a contravariance error.
     *
     * @template TBuilder of DatabaseObjectBuilder
     * @param \Closure(TBuilder, static): void $callback
     * @return static this field
     * @since 6.3
     */
    public function saveValueCallback(\Closure $callback): static;

    /**
     * Returns the callback set via `saveValueCallback()` or `null` if no such
     * callback has been set.
     *
     * @return ?\Closure(DatabaseObjectBuilder<*>, static): void
     * @since 6.3
     */
    public function getSaveValueCallback(): ?\Closure;

    /**
     * Sets a callback that loads this field's value from an `IStorableObject`
     * and returns this field.
     *
     * This is the counterpart to `saveValueCallback()`: while the save callback
     * writes the field's value into a builder, this callback reads the value
     * back out of an existing object when an edit form is populated. It is
     * invoked by `updatedObject()` and is expected to assign the value via
     * `$field->value()`, for example:
     *
     *     $field->loadValueCallback(
     *         static function (Tag $object, IFormField $formField) {
     *             $formField->value($object->name);
     *         }
     *     )
     *
     * When a callback is set it takes precedence over the default behaviour of
     * loading the value from the object property named after this field. Use it
     * when the value cannot be read from a single property, e.g. when it must be
     * derived from a related object or an additional query.
     *
     * The object type is a template parameter so that the callback may narrow it
     * to a concrete `IStorableObject` implementation (e.g. `Tag`) without
     * triggering a contravariance error.
     *
     * @template TObject of IStorableObject
     * @param \Closure(TObject, static): void $callback
     * @return static this field
     * @since 6.3
     */
    public function loadValueCallback(\Closure $callback): static;

    /**
     * Returns the callback set via `loadValueCallback()` or `null` if no such
     * callback has been set.
     *
     * @return ?\Closure(IStorableObject, static): void
     * @since 6.3
     */
    public function getLoadValueCallback(): ?\Closure;
}
