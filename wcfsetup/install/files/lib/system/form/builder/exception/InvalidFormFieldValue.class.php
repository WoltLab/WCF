<?php

namespace wcf\system\form\builder\exception;

use wcf\system\form\builder\field\IFormField;

/**
 * Exception to throw if an invalid value is given for a form field.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder
 * @since   5.5
 */
class InvalidFormFieldValue extends \InvalidArgumentException
{
    public function __construct(IFormField $field, string $expectedValue, string $givenValue)
    {
        parent::__construct("Given value is no {$expectedValue}, {$givenValue} given for field '{$field->getId()}'.");
    }
}
