<?php

namespace wcf\system\form\builder\data\processor;

use wcf\data\IStorableObject;
use wcf\system\form\builder\IFormDocument;

/**
 * Field data processor implementation that supports a custom processor callable.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class CustomFormDataProcessor extends AbstractFormDataProcessor
{
    /**
     * callable processing the form data
     * @var callable
     */
    private $formDataProcessor;

    /**
     * processor id primarily used for error messages
     */
    private string $id;

    /**
     * callable processing the object data
     * @var callable
     */
    private $objectDataProcessor;

    /**
     * Initializes a new CustomFormFieldDataProcessor object.
     *
     * @param $id processor id primarily used for error messages, does not have to be unique
     * @param callable(IFormDocument,array):array $formDataProcessor form data processor callable
     * @param callable(IFormDocument,array,IStorableObject):array $objectDataProcessor object data processor callable
     *
     * @throws  \InvalidArgumentException       if either id or processor callable are invalid
     */
    public function __construct(string $id, ?callable $formDataProcessor = null, ?callable $objectDataProcessor = null)
    {
        if (\preg_match('~^[a-z][A-z0-9-]*$~', $id) !== 1) {
            throw new \InvalidArgumentException("Invalid id '{$id}' given.");
        }

        $this->id = $id;

        if ($formDataProcessor === null && $objectDataProcessor === null) {
            throw new \InvalidArgumentException("No processors given.");
        }

        // validate form data processor function
        if ($formDataProcessor !== null) {
            $parameters = (new \ReflectionFunction($formDataProcessor))->getParameters();
            if (\count($parameters) !== 2) {
                throw new \InvalidArgumentException(
                    "The form data processor function must expect two parameters, instead " . \count($parameters)
                    . " parameter" . (\count($parameters) !== 1 ? 's' : '') . " are expected."
                );
            }

            /** @var \ReflectionType $parameterType */
            $parameterType = $parameters[0]->getType();
            if (
                !(
                    $parameterType instanceof \ReflectionNamedType
                && (
                    $parameterType->getName() === IFormDocument::class
                    || \is_subclass_of($parameterType->getName(), IFormDocument::class)
                )
                )
            ) {
                throw new \InvalidArgumentException(
                    "The form data processor function's first parameter must be an instance of '" . IFormDocument::class . "', instead "
                    . @($parameterType === null ? 'any' : "'" . $parameterType . "'") . " parameter is expected."
                );
            }

            $parameterType = $parameters[1]->getType();
            if (
                !(
                    ($parameterType instanceof \ReflectionNamedType
                && ($parameterType->getName() === 'array'))
                )
            ) {
                throw new \InvalidArgumentException(
                    "The form data processor function's second parameter must be an array."
                );
            }

            $this->formDataProcessor = $formDataProcessor;
        }

        // validate object data processor function
        if ($objectDataProcessor !== null) {
            $parameters = (new \ReflectionFunction($objectDataProcessor))->getParameters();
            if (\count($parameters) !== 3) {
                throw new \InvalidArgumentException(
                    "The object data processor function must expect three parameters, instead " . \count($parameters)
                    . " parameter" . (\count($parameters) !== 1 ? 's' : '') . " are expected."
                );
            }

            /** @var \ReflectionType $parameterType */
            $parameterType = $parameters[0]->getType();
            if (
                !(
                    $parameterType instanceof \ReflectionNamedType
                && (
                    $parameterType->getName() === IFormDocument::class
                    || \is_subclass_of($parameterType->getName(), IFormDocument::class)
                )
                )
            ) {
                throw new \InvalidArgumentException(
                    "The object data processor function's first parameter must be an instance of '" . IFormDocument::class . "', instead "
                    . @($parameterType === null ? 'any' : "'" . $parameterType . "'") . " parameter is expected."
                );
            }

            $parameterType = $parameters[1]->getType();
            if (
                !(
                    ($parameterType instanceof \ReflectionNamedType
                && ($parameterType->getName() === 'array'))
                )
            ) {
                throw new \InvalidArgumentException("The object data processor function's second parameter must be an array.");
            }

            $parameterType = $parameters[2]->getType();
            if (
                !(
                    ($parameterType instanceof \ReflectionNamedType
                && ($parameterType->getName() === IStorableObject::class))
                )
            ) {
                throw new \InvalidArgumentException("The object data processor function's third parameter must be an instance of '" . IStorableObject::class . "', instead " . @($parameterType === null ? 'any' : "'" . $parameterType . "'") . " parameter is expected.");
            }

            $this->objectDataProcessor = $objectDataProcessor;
        }
    }

    /**
     * Returns the id of the data processor (which is primarily used for error messages).
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function processFormData(IFormDocument $document, array $parameters)
    {
        if ($this->formDataProcessor === null) {
            return parent::processFormData($document, $parameters);
        }

        $parameters = \call_user_func($this->formDataProcessor, $document, $parameters);

        if (!\is_array($parameters)) {
            throw new \UnexpectedValueException("Field data processor '{$this->id}' does not return an array.");
        }

        return $parameters;
    }

    /**
     * @inheritDoc
     */
    public function processObjectData(IFormDocument $document, array $data, IStorableObject $object)
    {
        if ($this->objectDataProcessor === null) {
            return parent::processObjectData($document, $data, $object);
        }

        $data = \call_user_func($this->objectDataProcessor, $document, $data, $object);

        if (!\is_array($data)) {
            throw new \UnexpectedValueException("Field data processor '{$this->id}' does not return an array.");
        }

        return $data;
    }
}
