<?php

namespace wcf\system\object\filter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use wcf\data\DatabaseObject;

/**
 * Evaluates the stored filter values against individual objects.
 *
 * The filter values are stored as a JSON-encoded list of
 * `[filterIdentifier, serializedValue]` pairs. An object matches only if it
 * satisfies all filters (logical AND).
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
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
     * Returns whether the given object satisfies all stored filter values.
     * Values of unknown filters are ignored.
     *
     * Returns false if there are no active filters.
     *
     * @param TDatabaseObject $object
     * @throws MappingError if the stored filter values are malformed
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
     * Returns the available filters indexed by their identifier.
     *
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
     * Decodes the stored filter values into a list of
     * `[filterIdentifier, serializedValue]` pairs.
     *
     * @return list<array{0: string, 1: string}>
     * @throws MappingError if the stored filter values are malformed
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
