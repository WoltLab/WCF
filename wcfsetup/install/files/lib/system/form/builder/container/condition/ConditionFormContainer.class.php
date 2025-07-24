<?php

namespace wcf\system\form\builder\container\condition;

use wcf\data\IStorableObject;
use wcf\system\condition\provider\AbstractConditionProvider;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IFormNode;
use wcf\util\JSON;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ConditionFormContainer extends FormContainer
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_conditionFormContainer';

    /**
     * @phpstan-ignore missingType.generics
     */
    protected AbstractConditionProvider $conditionProvider;
    private bool $isRequired = false;
    private bool $isEmpty = false;

    public function __construct()
    {
        parent::__construct();
        $this->label("wcf.form.field.condition");
    }

    #[\Override]
    public function validate()
    {
        parent::validate();

        $this->isEmpty = !$this->hasChildren();
    }


    public function hasValidationErrors(): bool
    {
        if ($this->isRequired && $this->isEmpty) {
            return true;
        }

        return parent::hasValidationErrors();
    }


    #[\Override]
    protected static function getDefaultId(): string
    {
        return 'conditions';
    }

    #[\Override]
    public function isAvailable(): bool
    {
        return isset($this->conditionProvider);
    }

    #[\Override]
    public function readValues(): static
    {
        $prefixId = $this->getPrefixedId();

        if ($this->getDocument()->hasRequestData($prefixId)) {
            $conditions = $this->getDocument()->getRequestData($prefixId);

            foreach ($conditions as $index => $identifier) {
                $this->appendCondition($identifier, $index);
            }
        }

        return parent::readValues();
    }

    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        if ($loadValues && isset($data[$this->getPrefixedId()])) {
            $conditions = JSON::decode($data[$this->getPrefixedId()]);

            $data = $containers = [];
            foreach ($conditions as $index => $condition) {
                $containers[] = $this->appendCondition($condition['identifier'], $index);
                $fieldId = $this->getConditionProvider()->getFieldId($this->getPrefixedId(), $condition['identifier'], $index);
                $data[$fieldId] = $condition['value'];
            }

            foreach ($containers as $container) {
                /** @var IFormNode $child */
                foreach ($container->getIterator() as $child) {
                    if ($child instanceof IFormField || $child instanceof FormContainer) {
                        $child->updatedObject($data, $object);
                    }
                }
            }
        }

        return $this;
    }

    private function appendCondition(string $identifier, int $index): FormContainer
    {
        $prefixId = $this->getPrefixedId();

        $node = $this->getConditionProvider()->getConditionFormField($prefixId, $identifier, $index);
        $this->appendChild($node);

        $fieldId = $this->getConditionProvider()->getFieldId($this->getPrefixedId(), $identifier, $index);

        /** @var IFormNode $child */
        foreach ($node->getIterator() as $child) {
            $child->populate();
        }

        $this->getDocument()->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                "{$fieldId}DataProcessor",
                static function (IFormDocument $document, array $parameters) use ($prefixId, $identifier, $fieldId) {
                    $conditions = isset($parameters['data'][$prefixId]) ? JSON::decode($parameters['data'][$prefixId]) : [];

                    if (isset($parameters['data'][$fieldId]) || isset($parameters[$fieldId])) {
                        $conditions[] = [
                            "identifier" => $identifier,
                            "value" => $parameters['data'][$fieldId] ?? $parameters[$fieldId],
                        ];
                    }

                    unset($parameters['data'][$fieldId]);

                    $parameters['data'][$prefixId] = JSON::encode($conditions);

                    return $parameters;
                }
            )
        );

        return $node;
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function conditionProvider(AbstractConditionProvider $conditionProvider): self
    {
        $this->conditionProvider = $conditionProvider;

        return $this;
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function getConditionProvider(): AbstractConditionProvider
    {
        if (!isset($this->conditionProvider)) {
            throw new \BadMethodCallException(
                "Condition provider has not been set yet for node '{$this->getId()}'."
            );
        }

        return $this->conditionProvider;
    }

    public function getConditionProviderClass(): string
    {
        if (!isset($this->conditionProvider)) {
            throw new \BadMethodCallException(
                "Condition provider has not been set yet for node '{$this->getId()}'."
            );
        }

        return $this->conditionProvider::class;
    }

    public function required(bool $isRequired = true): self
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }
}
