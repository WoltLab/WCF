<?php

namespace wcf\system\view\filter;

use wcf\data\category\Category;
use wcf\data\DatabaseObjectList;
use wcf\system\category\CategoryHandler;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\view\filter\exception\InvalidFilterValue;

/**
 * Allows a column to be filtered on the basis of a select category.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class CategoryFilter extends AbstractFilter
{
    /**
     * @var list<int>|null
     */
    private ?array $availableCategoryIDs = null;

    /**
     * @param \Traversable<mixed> $options
     */
    public function __construct(
        private readonly \Traversable $options,
        string $id,
        string $languageItem = 'wcf.global.category',
        string $databaseColumn = ''

    ) {
        parent::__construct($id, $languageItem, $databaseColumn);
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return SelectFormField::create($this->id)
            ->label($this->languageItem)
            ->options($this->options, true);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $category = $this->getCategory($value);
        if ($category === null) {
            throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$this->id}' given.");
        }

        $columnName = $this->getDatabaseColumnName($list);

        $list->getConditionBuilder()->add("{$columnName} = ?", [$category->categoryID]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return $this->getCategory($value)?->getTitle() ?? '';
    }

    /**
     * Returns the category for the given value, but only if it is part of the
     * categories offered by this filter. `CategoryHandler` holds the categories
     * of every object type, therefore looking up the value there would accept
     * arbitrary categories, including inaccessible ones.
     */
    private function getCategory(string $value): ?Category
    {
        if (!\in_array((int)$value, $this->getAvailableCategoryIDs(), true)) {
            return null;
        }

        return CategoryHandler::getInstance()->getCategory((int)$value);
    }

    /**
     * @return list<int>
     */
    private function getAvailableCategoryIDs(): array
    {
        if ($this->availableCategoryIDs === null) {
            $this->availableCategoryIDs = \array_map(
                static fn($option) => (int)$option->getObjectID(),
                \iterator_to_array($this->options, false)
            );
        }

        return $this->availableCategoryIDs;
    }
}
