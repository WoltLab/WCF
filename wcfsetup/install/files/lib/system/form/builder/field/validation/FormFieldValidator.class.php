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
final class FormFieldValidator implements IFormFieldValidator
{
    /**
     * id of the validator that has to be unique for each field
     */
    protected string $id;

    /**
     * validation function
     * @var callable
     */
    protected $validator;

    #[\Override]
    public function __construct(string $id, callable $validator)
    {
        static::validateId($id);

        $this->id = $id;

        // validate validation function
        $parameters = (new \ReflectionFunction($validator))->getParameters();
        if (\count($parameters) !== 1) {
            throw new \InvalidArgumentException(
                "The validation function must expect one parameter, instead " . \count($parameters)
                    . " parameters are expected for validator '{$id}'."
            );
        }
        $parameterType = $parameters[0]->getType();
        if (
            !(
                $parameterType instanceof \ReflectionNamedType
                && (
                    $parameterType->getName() === IFormField::class
                    || \is_subclass_of($parameterType->getName(), IFormField::class)
                )
            )
        ) {
            throw new \InvalidArgumentException(
                "The validation function's parameter must be an instance of '" . IFormField::class . "', instead "
                    . @($parameterType === null ? 'any' : "'" . $parameterType . "'") . " parameter is expected for validator '{$id}'."
            );
        }

        $this->validator = $validator;
    }

    #[\Override]
    public function __invoke(IFormField $field): void
    {
        \call_user_func($this->validator, $field);
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Checks if the given parameter is a and a valid validator id.
     *
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public static function validateId(string $id): void
    {
        if (\preg_match('~^[a-z][_A-Za-z0-9-]*$~', $id) !== 1) {
            throw new \InvalidArgumentException("Invalid id '{$id}' given.");
        }
    }
}
