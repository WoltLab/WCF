<?php

namespace wcf\system\form\builder\field;

use wcf\util\HtmlString;

/**
 * Represents a form field that supports displaying a suffix.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface ISuffixedFormField extends IFormField
{
    /**
     * Returns the suffix of this field or `null` if no suffix has been set.
     *
     * @return ?string
     */
    public function getSuffix();

    /**
     * Sets the suffix of this field and returns this field. If `null` is
     * passed, the suffix is removed.
     *
     * The suffix is HTML. A string is a phrase name, resolved with `$variables`,
     * and the resolved phrase is trusted HTML; a string that is no phrase name
     * is used as is. An `HtmlString` is used as is and never resolved as a
     * phrase. Plain text must be wrapped with `HtmlString::fromText()`.
     *
     * @param array<string, mixed> $variables additional variables used when resolving the phrase
     * @return static this field
     *
     * @throws \InvalidArgumentException if variables are given together with an `HtmlString` or `null`
     */
    public function suffix(HtmlString|string|null $languageItem = null, array $variables = []);
}
