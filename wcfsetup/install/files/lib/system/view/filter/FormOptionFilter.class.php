<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\option\IFormOption;

/**
 * Filter for columns that contain form option values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class FormOptionFilter extends AbstractFilter
{
    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(
        private readonly IFormOption $option,
        private readonly array $configuration,
        string $id,
        string $languageItem,
        string $databaseColumn = ''
    ) {
        parent::__construct($id, $languageItem, $databaseColumn);
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return $this->option->getFilterFormField($this->id, $this->configuration)->label($this->languageItem);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $this->option->applyFilter($list, $this->getDatabaseColumnName($list), $value);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return $this->option->renderFilterValue($value, $this->configuration);
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return $this->option->serializeValue($value);
    }

    #[\Override]
    public function unserializeValue(string $value): mixed
    {
        return $this->option->unserializeValue($value);
    }
}
