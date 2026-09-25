<?php

namespace wcf\system\object\filter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use wcf\data\DatabaseObject;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\WCF;

/**
 * @template TDatabaseObject of DatabaseObject
 */
class ObjectFilterHandler
{
    /**
     * @param list<IObjectFilter<TDatabaseObject, mixed>> $filters
     */
    public function __construct(
        private readonly array $filters,
    ) {}

    /**
     * This method is intended to eventually take over the generation of the form in `ObjectFilterBuilderAction`.
     */
    public function getFormFields(): void
    {
        $filters = [];
        foreach ($this->filters as $filter) {
            $filters[$filter->getIdentifier()] = $filter->getTitle();
        }

        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $filters,
            static fn($a, $b) => $collator->compare($a, $b)
        );

        $selection = SelectFormField::create('filter')
            ->options($filters)
            ->required();
    }

    /**
     * @param TDatabaseObject $object
     * @throws MappingError
     */
    public function testObject(DatabaseObject $object, ?string $json): bool
    {
        $values = $this->unserializeValues($json);
        if ($values === []) {
            return false;
        }

        $filters = $this->getFiltersByIdentifier();

        $hasActiveFilters = false;
        foreach ($values as [$identifier, $serializedValue]) {
            $filter = $filters[$identifier] ?? null;
            if ($filter === null) {
                continue;
            }

            $hasActiveFilters = true;
            if (!$filter->testObject($object, $filter->unserializeValue($serializedValue))) {
                return false;
            }
        }

        // Returns true if there are active filters because any negative test
        // would have returned early.
        return $hasActiveFilters;
    }

    /**
     * @return array<string, IObjectFilter<TDatabaseObject, mixed>>
     */
    protected function getFiltersByIdentifier(): array
    {
        $filters = [];
        foreach ($this->filters as $filter) {
            $filters[$filter->getIdentifier()] = $filter;
        }

        return $filters;
    }

    /**
     * @return list<array{0: string, 1: string}>
     * @throws MappingError
     */
    protected function unserializeValues(?string $json): array
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

        return $values;
    }
}
