<?php

namespace wcf\system\object\filter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\object\filter\user\IUserObjectFilter;
use wcf\system\WCF;

final class ObjectFilterHandler
{
    /**
     * @param list<IUserObjectFilter<mixed>> $filters
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
     * @throws MappingError
     */
    public function applyFilters(PreparedStatementConditionBuilder $conditions, ?string $json): void
    {
        $values = $this->unserializeValues($json);
        if ($values === []) {
            $conditions->add('1=0');

            return;
        }

        $filters = $this->getFiltersByIdentifier();

        $hasActiveFilters = false;
        foreach ($values as [$identifier, $serializedValue]) {
            $filter = $filters[$identifier] ?? null;
            if ($filter === null) {
                continue;
            }

            $filter->applyFilter(
                $conditions,
                $filter->unserializeValue($serializedValue),
            );
            $hasActiveFilters = true;
        }

        if (!$hasActiveFilters) {
            $conditions->add('1=0');
        }
    }

    /**
     * @throws MappingError
     */
    public function testUser(User $user, ?string $json): bool
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
            if (!$filter->testUser($user, $filter->unserializeValue($serializedValue))) {
                return false;
            }
        }

        // Returns true if there are active filters because any negative test
        // would have returned early.
        return $hasActiveFilters;
    }

    /**
     * @return array<string, IUserObjectFilter<mixed>>
     */
    private function getFiltersByIdentifier(): array
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
    private function unserializeValues(?string $json): array
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
