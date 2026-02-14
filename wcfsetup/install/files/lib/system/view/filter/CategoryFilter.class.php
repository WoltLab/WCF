<?php

namespace wcf\system\view\filter;

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
        $category = CategoryHandler::getInstance()->getCategory((int)$value);
        if ($category === null) {
            throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$this->id}' given.");
        }

        $columnName = $this->getDatabaseColumnName($list);

        $list->getConditionBuilder()->add("{$columnName} = ?", [$value]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return CategoryHandler::getInstance()->getCategory((int)$value)->getTitle();
    }
}
