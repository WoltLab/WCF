<?php

namespace wcf\system\form\option;

use wcf\data\DatabaseObjectList;
use wcf\system\database\table\column\AbstractDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\CurrencyFormField;
use wcf\system\form\option\formatter\CurrencyFormatter;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\util\StringUtil;

/**
 * Implementation of a form field for currency values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class CurrencyFormOption extends AbstractNumericFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'currency';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        $formField = CurrencyFormField::create($id);
        if (isset($configuration['currency'])) {
            $formField->currency($configuration['currency']);
        }
        if (isset($configuration['minFloatValue'])) {
            $formField->minimum($configuration['minFloatValue']);
        }
        if (isset($configuration['maxFloatValue'])) {
            $formField->maximum($configuration['maxFloatValue']);
        }

        return $formField;
    }

    #[\Override]
    public function getConfigurationFormFields(): array
    {
        return ['currency', 'minFloatValue', 'maxFloatValue', 'required'];
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new CurrencyFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return $this->getFormatter();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $columnName, mixed $value): void
    {
        $values = $this->parseFilterValue($value);

        if (!$values['from'] && !$values['to']) {
            return;
        }

        if (!$values['to']) {
            $list->getConditionBuilder()->add("{$columnName} >= ?", [$values['from'] * 100]);
        } else {
            $list->getConditionBuilder()->add("{$columnName} BETWEEN ? AND ?", [$values['from'] * 100, $values['to'] * 100]);
        }
    }

    #[\Override]
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn
    {
        return IntDatabaseTableColumn::create($name);
    }

    #[\Override]
    protected function getSuffix(array $configuration): string
    {
        if (!empty($configuration['currency'])) {
            return StringUtil::encodeHTML($configuration['currency']);
        }

        return '';
    }
}
