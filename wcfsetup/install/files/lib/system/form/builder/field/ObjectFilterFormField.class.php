<?php

namespace wcf\system\form\builder\field;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use Override;
use wcf\action\ObjectFilterBuilderAction;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\TObjectTypeFormNode;
use wcf\system\object\filter\builder\IObjectFilterBuilder;
use wcf\system\request\LinkHandler;

final class ObjectFilterFormField extends AbstractFormField
{
    use TObjectTypeFormNode;

    protected $templateName = 'shared_objectFilterFormField';

    #[Override]
    public function readValue(): ObjectFilterFormField
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        return $this;
    }

    #[Override]
    public function validate(): void
    {
        try {
            $values = $this->unserializeFilters($this->getValue());
        } catch (MappingError) {
            $this->addValidationError(
                new FormFieldValidationError('malformedJson')
            );

            return;
        }

        if ($this->isRequired() && $values === []) {
            $this->addValidationError(
                new FormFieldValidationError('empty')
            );
        }

        // TODO: debug only
        $this->addValidationError(
            new FormFieldValidationError('empty')
        );
    }

    #[Override]
    public function getObjectTypeDefinition(): string
    {
        return 'com.woltlab.wcf.objectFilter';
    }

    public function toJson(): string
    {
        return \json_encode(
            $this->unserializeFilters($this->getValue()),
            \JSON_THROW_ON_ERROR,
        );
    }

    public function getEndpoint(): string
    {
        return LinkHandler::getInstance()->getControllerLink(
            ObjectFilterBuilderAction::class,
            [
                'objectType' => $this->getObjectType()->objectType,
            ],
        );
    }

    /**
     * @return list<array{
     *  identifier: string,
     *  summary: string,
     *  value: string,
     * }>
     * @throws MappingError
     */
    private function unserializeFilters(?string $json): array
    {
        if ($json === null) {
            return [];
        }

        /** @var list<array{0: string, 1: string}> $values */
        $values = (new MapperBuilder())->mapper()->map(
            <<<'EOT'
                list<array{0: string, 1: string}>
                EOT,
            Source::json($json)
        );

        if ($values === []) {
            return $values;
        }

        /** @var IObjectFilterBuilder $builder */
        $builder = $this->getObjectType()->getProcessor();
        $filters = [];
        foreach ($builder->getFilters() as $filter) {
            $filters[$filter->getIdentifier()] = $filter;
        }

        return \array_map(
            function (array $value) use ($filters): array {
                [$identifier, $serializedValue] = $value;
                $filter = $filters[$identifier];

                return [
                    'identifier' => $identifier,
                    'summary' => $filter->summarizeValue(
                        $filter->unserializeValue($serializedValue),
                    ),
                    'value' => $serializedValue,
                ];
            },
            \array_filter(
                $values,
                static fn($value) => isset($filters[$value[0]]),
            ),
        );
    }
}
