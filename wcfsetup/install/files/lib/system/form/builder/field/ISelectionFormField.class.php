<?php

namespace wcf\system\form\builder\field;

use wcf\data\DatabaseObjectList;

/**
 * Represents a form field that consists of a predefined set of possible values.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface ISelectionFormField extends IFormField
{
    /**
     * Returns a structured array that can be used to generate the form field output.
     *
     * The labels are HTML and must be output as is, without further encoding.
     *
     * @return ?array<string|int, array{label: string, value: mixed, depth: int, isSelectable: bool}>
     * @throws \BadMethodCallException if nested options are not supported
     */
    public function getNestedOptions();

    /**
     * Returns the possible options of this field.
     *
     * The array maps the values to their labels. The labels are HTML and must
     * be output as is, without further encoding.
     *
     * @return mixed[]
     *
     * @throws \BadMethodCallException if no options have been set
     */
    public function getOptions();

    /**
     * Sets the possible choices of this field and returns this field.
     *
     * Labels are plain text and HTML-encoded by this method: strings, integers,
     * floats and objects implementing `__toString()` or `ITitledObject` (also
     * decorated ones). The only exception are `HtmlString` labels, which are
     * output as is. Thus, no label ends up as unencoded markup unless the caller
     * explicitly wrapped it in `HtmlString::fromSafeHtml()`. Labels are never
     * resolved as phrases, even if they look like a phrase name; see
     * `phraseChoices()` for that.
     *
     * `$choices` is either an array mapping the values to their labels, a
     * `\Traversable`, or a `\Closure` returning one of both. An array is always
     * data and never invoked, even if PHP considers it callable. A
     * `DatabaseObjectList` whose objects have not been read yet is read
     * automatically.
     *
     * If `$nested` is `true`, `$choices` is either a traversable of `IObjectTreeNode`
     * objects, or a list of arrays with the entries `depth` (non-negative `int`),
     * `label`, `value` and, optionally, `isSelectable` (defaults to `true`).
     *
     * Note: If PHP considers the key of the first selectable choice to be empty
     * and this field is nullable, then the save value of that key is `null`
     * instead of the given empty value.
     *
     * @param array<string|int, mixed>|\Closure|\Traversable<mixed> $choices
     * @throws \InvalidArgumentException if the choices are invalid
     * @throws \UnexpectedValueException if the closure does not return an array or a `\Traversable`
     * @since 6.3
     */
    public function choices(array|\Closure|\Traversable $choices, bool $nested = false): static;

    /**
     * Sets the possible choices of this field to the given phrases and returns
     * this field.
     *
     * `$choices` maps the values to phrase names. Each phrase is resolved with
     * the active language and output as HTML, like `{lang}` in templates. A label
     * that is no phrase name is rejected, so that no arbitrary string ends up as
     * unencoded markup; a missing phrase is shown as its name.
     *
     * Use `choices()` with `HtmlString::fromSafeHtml(WCF::getLanguage()->get('…'))`
     * for lists mixing phrases with other labels.
     *
     * @param array<string|int, string> $choices
     * @throws \InvalidArgumentException if a label is no phrase name
     * @since 6.3
     */
    public function phraseChoices(array $choices): static;

    /**
     * Sets the possible options of this field and returns this field.
     *
     * Note: If PHP considers the key of the first selectable option to be empty
     * and the this field is nullable, then the save value of that key is `null`
     * instead of the given empty value.
     *
     * If a `callable` is passed, it is expected that it either returns an array
     * or a `DatabaseObjectList` object.
     *
     * If a `DatabaseObjectList` object is passed and `$options->objectIDs === null`,
     * `$options->readObjects()` is called so that the `readObjects()` does not have
     * to be called by the API user.
     *
     * If nested options are passed, the given options must be a array or a
     * callable returning an array. Each array value must be an array with the
     * following entries: `depth`, `label`, and `value`.
     *
     * String labels are output as HTML and have to be encoded by the caller. Labels
     * derived from objects via `__toString()` or `ITitledObject::getTitle()` are
     * encoded automatically, `HtmlString` labels are output as is.
     *
     * @param array<string|int, mixed>|callable|\Traversable<mixed>|DatabaseObjectList $options selectable options or callable returning the options
     * @param bool $nestedOptions is `true` if the passed options are nested options
     * @param bool $labelLanguageItems is `true` if the labels should be treated as language items if possible
     * @return static this field
     *
     * @throws \InvalidArgumentException if given options are no array or callable or otherwise invalid
     * @throws \UnexpectedValueException if callable does not return an array
     * @deprecated 6.3 Use `choices()` or `phraseChoices()` instead.
     */
    public function options(array|callable|\Traversable|DatabaseObjectList $options, bool $nestedOptions = false, bool $labelLanguageItems = true);

    /**
     * Returns `true` if the field class supports nested options and `false` otherwise.
     *
     * @return bool
     */
    public function supportsNestedOptions();
}
