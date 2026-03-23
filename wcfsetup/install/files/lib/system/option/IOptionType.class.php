<?php

namespace wcf\system\option;

use wcf\data\option\Option;

/**
 * Any option type has to implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IOptionType
{
    /**
     * Returns the html code of the form element for the given option of this
     * option type.
     *
     * @param Option $option
     * @return  string
     */
    public function getFormElement(Option $option, mixed $value);

    /**
     * Validates the input for the given option of this option type and throws
     * a wcf\system\exception\UserInputException if the validation should fail.
     *
     * @param Option $option
     * @return void
     */
    public function validate(Option $option, mixed $newValue);

    /**
     * Returns the value of the given option of this option type which will
     * be saved in the database.
     *
     * @param Option $option
     * @return  string
     */
    public function getData(Option $option, mixed $newValue);

    /**
     * Returns the css class name for this option type.
     *
     * @return  string
     */
    public function getCSSClassName();

    /**
     * Returns true if options supports internationalization.
     *
     * @return  bool
     */
    public function supportI18n();

    /**
     * Compares two values and returns a PHP-like comparison result.
     *
     *   $value1 < $value2  => -1
     *   $value1 == $value2 => 0
     *   $value1 > $value2  => 1
     *
     *
     * @return  int
     */
    public function compare(mixed $value1, mixed $value2);

    /**
     * Returns true if option's label is hidden in search form.
     *
     * @return  bool
     */
    public function hideLabelInSearch();

    /**
     * Determines disabled options by given option value.
     *
     * @return      string[]
     * @since       5.2
     */
    public function getDisabledOptionNames(mixed $value, string $enableOptions);
}
