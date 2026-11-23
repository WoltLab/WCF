<?php

namespace wcf\system\form\builder\field;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use wcf\action\ObjectFilterBuilderAction;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\object\filter\builder\IObjectFilterBuilder;
use wcf\system\object\filter\IObjectFilter;
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
     * @var ?array<string, IObjectFilter<*, mixed>>
     */
    private ?array $filtersByIdentifier = null;

    /**
     * Sets the builder that provides the available filters.
     *
     * @param IObjectFilterBuilder<*> $builder
     */
    public function builder(IObjectFilterBuilder $builder): static
    {
        $this->builder = $builder;
        $this->filtersByIdentifier = null;

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
        if (
            $this->getDocument()->hasRequestData($this->getPrefixedId())
            && \is_string($this->getDocument()->getRequestData($this->getPrefixedId()))
        ) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        return $this;
    }

    /**
     * Returns the configured filters as a JSON-encoded list of
     * `[filterIdentifier, serializedValue]` pairs. Values of unknown filters
     * are removed.
     *
     * @throws InvalidSource|MappingError if the value of this field is malformed
     */
    #[\Override]
    public function getSaveValue(): ?string
    {
        if ($this->getValue() === null) {
            return null;
        }

        return \json_encode(
            $this->decodeKnownFilters($this->getValue()),
            \JSON_THROW_ON_ERROR,
        );
    }

    #[\Override]
    public function validate(): void
    {
        try {
            $values = $this->unserializeFilters($this->getValue());
        } catch (InvalidSource|MappingError) {
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

        $filters = $this->getFiltersByIdentifier();

        $counts = \array_count_values(\array_column($values, 'identifier'));
        foreach ($counts as $identifier => $count) {
            $filter = $filters[$identifier];
            if ($count > 1 && !$filter->isRepeatable()) {
                $this->addValidationError(
                    new FormFieldValidationError(
                        'notRepeatable',
                        'wcf.objectFilter.error.notRepeatable',
                        ['title' => $filter->getTitle()]
                    )
                );
            }
        }
    }

    /**
     * Returns the configured filters as JSON for the client-side filter builder.
     * Each filter consists of its identifier, a human-readable summary and the
     * serialized value. A malformed value results in an empty list, the
     * corresponding validation error is reported by `validate()`.
     */
    public function toJson(): string
    {
        try {
            $filters = $this->unserializeFilters($this->getValue());
        } catch (InvalidSource|MappingError) {
            $filters = [];
        }

        return \json_encode($filters, \JSON_THROW_ON_ERROR);
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
     * @throws InvalidSource|MappingError if the given value is malformed
     */
    private function unserializeFilters(?string $json): array
    {
        $filters = $this->getFiltersByIdentifier();

        return \array_map(
            static function (array $value) use ($filters): array {
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
            $this->decodeKnownFilters($json),
        );
    }

    /**
     * Decodes the given value into a list of `[filterIdentifier, serializedValue]`
     * pairs. Values of unknown filters are removed.
     *
     * @return list<array{0: string, 1: string}>
     * @throws InvalidSource|MappingError if the given value is malformed
     */
    private function decodeKnownFilters(?string $json): array
    {
        if ($json === null) {
            return [];
        }

        /** @var list<array{0: string, 1: string}> $values */
        $values = new MapperBuilder()->mapper()->map(
            <<<'EOT'
                list<array{0: string, 1: string}>
                EOT,
            Source::json($json)
        );

        if ($values === []) {
            return $values;
        }

        $filters = $this->getFiltersByIdentifier();

        return \array_values(\array_filter(
            $values,
            static fn($value) => isset($filters[$value[0]]),
        ));
    }

    /**
     * Returns the filters of the builder indexed by their identifier.
     *
     * @return array<string, IObjectFilter<*, mixed>>
     */
    private function getFiltersByIdentifier(): array
    {
        if ($this->filtersByIdentifier === null) {
            $this->filtersByIdentifier = [];
            foreach ($this->getBuilder()->getFilters() as $filter) {
                $this->filtersByIdentifier[$filter->getIdentifier()] = $filter;
            }
        }

        return $this->filtersByIdentifier;
    }

    #[\Override]
    public function getLabelTarget(): ?string
    {
        return null;
    }
}
