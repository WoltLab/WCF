<?php

namespace wcf\system\form\builder\field\validation;

use wcf\system\form\builder\field\IFormField;

/**
 * Validates the value of a form field.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IFormFieldValidator
{
    /**
     * Initializes a new validator.
     *
     * @throws \InvalidArgumentException if the given id is invalid
     */
    public function __construct(string $id, callable $validator);

    /**
     * Validates the value of the given field.
     *
     * @return void
     */
    public function __invoke(IFormField $field);

    /**
     * Returns the id of the validator.
     *
     * @return string id of the dependency
     */
    public function getId();
}
