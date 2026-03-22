<?php

namespace wcf\system\form\builder\field;

use wcf\data\language\Language;

/**
 * Represents a form field that supports setting the maximum length of the field value.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IMaximumLengthFormField extends IFormField
{
    /**
     * Returns the maximum length of the values of this field or `null` if no maximum
     * length has been set.
     *
     * @return ?int
     */
    public function getMaximumLength();

    /**
     * Sets the maximum length of the values of this field. If `null` is passed, the
     * maximum length is removed.
     *
     * @return static this field
     *
     * @throws  \InvalidArgumentException   if the given maximum length is no integer or otherwise invalid
     */
    public function maximumLength(?int $maximumLength = null);

    /**
     * Validates the maximum length of the given text.
     *
     * @return void
     */
    public function validateMaximumLength(string $text, ?Language $language = null);
}
