<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\view\filter\exception\InvalidFilterValue;
use wcf\system\WCF;

/**
 * Allows a column to be filtered on the basis of a select dropdown.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class SelectFilter extends AbstractFilter
{
    /**
     * @param array<string|int, mixed> $options
     */
    public function __construct(
        protected readonly array $options,
        string $id,
        string $languageItem,
        string $databaseColumn = '',
        protected readonly bool $labelLanguageItems = true
    ) {
        parent::__construct($id, $languageItem, $databaseColumn);
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return SelectFormField::create($this->id)
            ->label($this->languageItem)
            ->options($this->options, labelLanguageItems: $this->labelLanguageItems);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        if (!isset($this->options[$value])) {
            throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$this->id}' given.");
        }

        $columnName = $this->getDatabaseColumnName($list);

        $list->getConditionBuilder()->add("{$columnName} = ?", [$value]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        if ($this->labelLanguageItems) {
            return WCF::getLanguage()->get($this->options[$value]);
        } else {
            return $this->options[$value];
        }
    }
}
