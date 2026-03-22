<?php

namespace wcf\system\form\builder\field;

use wcf\data\language\Language;

/**
 * Represents a form field that supports setting the minimum length of the field value.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IMinimumLengthFormField extends IFormField
{
    /**
     * Returns the minimum length of the values of this field or `null` if no minimum
     * length has been set.
     *
     * @return ?int
     */
    public function getMinimumLength();

    /**
     * Sets the minimum length of the values of this field. If `null` is passed, the
     * minimum length is removed.
     *
     * @return static this field
     *
     * @throws \InvalidArgumentException if the given minimum length is no int or otherwise invalid
     */
    public function minimumLength(?int $minimumLength = null);

    /**
     * Validates the minimum length of the given text.
     *
     * @return void
     */
    public function validateMinimumLength(string $text, ?Language $language = null);
}
