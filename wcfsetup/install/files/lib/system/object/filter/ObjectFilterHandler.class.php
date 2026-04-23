<?php

namespace wcf\system\object\filter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\object\filter\builder\IObjectFilterBuilder;
use wcf\system\WCF;

final class ObjectFilterHandler
{
    /**
     * @param list<IObjectFilter<mixed>> $filters
     */
    public function __construct(
        private readonly array $filters,
    ) {}

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
    public function applyFilters(PreparedStatementConditionBuilder $conditions, string $objectTypeName, ?string $json): void
    {
        $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.objectFilter',
            $objectTypeName,
        );
        if ($objectType === null) {
            throw new InvalidObjectTypeException($objectTypeName, 'com.woltlab.wcf.objectFilter');
        }

        if ($json === null) {
            $conditions->add('1=0');

            return;
        }

        /** @var list<array{0: string, 1: string}> $values */
        $values = (new MapperBuilder())->mapper()->map(
            <<<'EOT'
                list<array{0: string, 1: string}>
                EOT,
            Source::json($json)
        );

        if ($values === []) {
            $conditions->add('1=0');

            return;
        }

        /** @var IObjectFilterBuilder $builder */
        $builder = $objectType->getProcessor();
        $filters = [];
        foreach ($builder->getFilters() as $filter) {
            $filters[$filter->getIdentifier()] = $filter;
        }

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
}
