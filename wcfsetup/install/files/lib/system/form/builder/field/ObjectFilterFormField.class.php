<?php

namespace wcf\system\form\builder\field;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use wcf\action\ObjectFilterBuilderAction;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\object\filter\builder\IObjectFilterBuilder;
use wcf\system\request\LinkHandler;

/**
 * Form field to configure a list of object filters provided by an object filter builder.
 *
 * The value is stored as a JSON-encoded list of `[filterIdentifier, serializedValue]`
 * pairs, which is the format expected by `ObjectFilterHandler`.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ObjectFilterFormField extends AbstractFormField
{
    protected $templateName = 'shared_objectFilterFormField';

    /**
     * @var IObjectFilterBuilder<*>
     */
    private IObjectFilterBuilder $builder;

    /**
     * Sets the builder that provides the available filters.
     *
     * @param IObjectFilterBuilder<*> $builder
     */
    public function builder(IObjectFilterBuilder $builder): static
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Returns the builder that provides the available filters.
     *
     * @return IObjectFilterBuilder<*>
     * @throws \BadMethodCallException if no builder has been set
     */
    public function getBuilder(): IObjectFilterBuilder
    {
        if (!isset($this->builder)) {
            throw new \BadMethodCallException("Builder has not been set for field '{$this->getId()}'.");
        }

        return $this->builder;
    }

    #[\Override]
    public function readValue(): ObjectFilterFormField
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        return $this;
    }

    #[\Override]
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
    }

    /**
     * Returns the configured filters as JSON for the client-side filter builder.
     * Each filter consists of its identifier, a human-readable summary and the
     * serialized value.
     *
     * @throws MappingError if the value of this field is malformed
     */
    public function toJson(): string
    {
        return \json_encode(
            $this->unserializeFilters($this->getValue()),
            \JSON_THROW_ON_ERROR,
        );
    }

    /**
     * Returns the URL of the dialog that is used to add a new filter.
     */
    public function getEndpoint(): string
    {
        return LinkHandler::getInstance()->getControllerLink(
            ObjectFilterBuilderAction::class,
            [
                'identifier' => $this->getBuilder()->getIdentifier(),
            ],
        );
    }

    /**
     * Decodes the given value and enriches each filter with a human-readable
     * summary. Values of unknown filters are ignored.
     *
     * @return list<array{
     *  identifier: string,
     *  summary: string,
     *  value: string,
     * }>
     * @throws MappingError if the given value is malformed
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

        $filters = [];
        foreach ($this->getBuilder()->getFilters() as $filter) {
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
            \array_values(\array_filter(
                $values,
                static fn($value) => isset($filters[$value[0]]),
            )),
        );
    }
}
