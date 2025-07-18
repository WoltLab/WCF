<?php

namespace wcf\system\gridView\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\gridView\filter\exception\InvalidFilterValue;
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
        private readonly array $options,
        string $databaseColumn = '',
        protected readonly bool $labelLanguageItems = true
    ) {
        parent::__construct($databaseColumn);
    }

    #[\Override]
    public function getFormField(string $id, string $label): AbstractFormField
    {
        return SelectFormField::create($id)
            ->label($label)
            ->options($this->options, labelLanguageItems: $this->labelLanguageItems);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
    {
        if (!isset($this->options[$value])) {
            throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$id}' given.");
        }

        $columnName = $this->getDatabaseColumnName($list, $id);

        $list->getConditionBuilder()->add("{$columnName} = ?", [$value]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return WCF::getLanguage()->get($this->options[$value]);
    }
}
