<?php

namespace wcf\system\exception;

/**
 * UserInputException handles all formular input errors.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserInputException extends UserException
{
    /**
     * name of error field
     * @var string
     */
    protected $field;

    /**
     * error type
     * @var string|mixed[]
     */
    protected $type;

    /**
     * variables for AJAX error handling
     * @var array<string, mixed>
     */
    protected $variables = [];

    /**
     * @param string $field affected formular field
     * @param string|mixed[] $type kind of this error
     * @param array<string, mixed> $variables additional variables for AJAX error handling
     */
    public function __construct(string $field = '', string|array $type = 'empty', array $variables = [])
    {
        $this->field = $field;
        $this->type = $type;
        $this->variables = $variables;
        $this->message = 'Parameter ' . $field . ' is missing or invalid';

        parent::__construct();
    }

    /**
     * Returns the affected formular field of this error.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Returns the kind of this error.
     *
     * @return string|mixed[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns additional variables for AJAX error handling.
     *
     * @return array<string, mixed>
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
