<?php

namespace wcf\system\form\builder\container;

use wcf\data\IStorableObject;
use wcf\system\condition\provider\AbstractConditionProvider;
use wcf\system\condition\type\IConditionType;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\util\JSON;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @phpstan-type ConditionProvider AbstractConditionProvider<IConditionType<mixed>>
 */
final class ConditionFormContainer extends FormContainer
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_conditionFormContainer';
    private int $lastConditionIndex = 0;

    /**
     * @var ConditionProvider
     */
    protected AbstractConditionProvider $conditionProvider;

    public function __construct()
    {
        parent::__construct();
        $this->label("wcf.form.field.condition");
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

                $this->lastConditionIndex = \max($this->lastConditionIndex, $index);
            }
        }

        return parent::readValues();
    }

    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        if ($loadValues && isset($data[$this->getPrefixedId()])) {
            $conditions = JSON::decode($data[$this->getPrefixedId()]);

            foreach ($conditions as $index => $condition) {
                $this->appendCondition($condition['identifier'], $index, $condition['value']);

                $this->lastConditionIndex = \max($this->lastConditionIndex, $index);
            }
        }

        return $this;
    }

    private function appendCondition(string $identifier, int $index, mixed $value = null): void
    {
        $prefixId = $this->getPrefixedId();

        $formField = $this->getConditionProvider()->getConditionFormField($prefixId, $identifier, $index, $value);
        $this->appendChild($formField);

        $fieldId = $this->getConditionProvider()->getFieldId($prefixId, $identifier, $index);

        $this->getDocument()->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                "{$fieldId}DataProcessor",
                static function (IFormDocument $document, array $parameters) use ($prefixId, $identifier, $fieldId) {
                    $conditions = isset($parameters['data'][$prefixId]) ? JSON::decode($parameters['data'][$prefixId]) : [];

                    if (isset($parameters['data'][$fieldId])) {
                        $conditions[] = [
                            "identifier" => $identifier,
                            "value" => $parameters['data'][$fieldId],
                        ];
                    }

                    unset($parameters['data'][$fieldId]);

                    $parameters['data'][$prefixId] = JSON::encode($conditions);

                    return $parameters;
                }
            )
        );
    }

    /**
     * @param ConditionProvider $conditionProvider
     *
     * @return self<ConditionProvider>
     */
    public function conditionProvider(AbstractConditionProvider $conditionProvider): self
    {
        $this->conditionProvider = $conditionProvider;

        return $this;
    }

    /**
     * @return ConditionProvider
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

    public function getLastConditionIndex(): int
    {
        return $this->lastConditionIndex;
    }
}
