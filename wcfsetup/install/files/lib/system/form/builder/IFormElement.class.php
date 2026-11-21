<?php

namespace wcf\system\form\builder;

use wcf\util\HtmlString;

/**
 * Represents an element of a form that can have a description, a label and dependencies.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IFormElement extends IFormNode
{
    /**
     * Sets the description of this element and returns this element. If `null`
     * is passed, the element description is removed.
     *
     * The description is HTML. A string is a phrase name, resolved with
     * `$variables`, and the resolved phrase is trusted HTML; a string that is
     * no phrase name is used as is. An `HtmlString` is used as is and never
     * resolved as a phrase. Plain text must be wrapped with `HtmlString::fromText()`.
     *
     * @param array<string, mixed> $variables additional variables used when resolving the phrase
     * @return static this element
     *
     * @throws \InvalidArgumentException if variables are given together with an `HtmlString` or `null`
     */
    public function description(HtmlString|string|null $languageItem = null, array $variables = []);

    /**
     * Returns the description of this element or `null` if no description has been set.
     *
     * @return ?string element description
     */
    public function getDescription();

    /**
     * Returns the label of this element or `null` if no label has been set.
     *
     * @return ?string element label
     */
    public function getLabel();

    /**
     * Sets the label of this element and returns this element. If `null` is
     * passed, the element label is removed.
     *
     * The label is HTML. A string is a phrase name, resolved with `$variables`,
     * and the resolved phrase is trusted HTML; a string that is no phrase name
     * is used as is. An `HtmlString` is used as is and never resolved as a
     * phrase. Plain text must be wrapped with `HtmlString::fromText()`.
     *
     * @param array<string, mixed> $variables additional variables used when resolving the phrase
     * @return static this element
     *
     * @throws \InvalidArgumentException if variables are given together with an `HtmlString` or `null`
     */
    public function label(HtmlString|string|null $languageItem = null, array $variables = []);

    /**
     * Returns `true` if this element requires a label to be set.
     *
     * @return bool
     */
    public function requiresLabel();
}
