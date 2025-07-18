<?php

namespace wcf\system\gridView\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\category\CategoryHandler;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\gridView\filter\exception\InvalidFilterValue;

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
     * @param \Traversable<mixed> $options
     */
    public function __construct(private readonly \Traversable $options, string $databaseColumn = '')
    {
        parent::__construct($databaseColumn);
    }

    #[\Override]
    public function getFormField(string $id, string $label): AbstractFormField
    {
        return SelectFormField::create($id)
            ->label($label)
            ->options($this->options, true);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
    {
        $category = CategoryHandler::getInstance()->getCategory((int)$value);
        if ($category === null) {
            throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$id}' given.");
        }

        $columnName = $this->getDatabaseColumnName($list, $id);

        $list->getConditionBuilder()->add("{$columnName} = ?", [$category->categoryID]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return CategoryHandler::getInstance()->getCategory((int)$value)->getTitle();
    }
}
