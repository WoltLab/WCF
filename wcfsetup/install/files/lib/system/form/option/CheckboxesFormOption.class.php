<?php

namespace wcf\system\form\option;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\system\form\option\formatter\MultipleSelectionFormatter;
use wcf\system\WCF;

/**
 * Implementation of a form field for selecting multiple values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class CheckboxesFormOption extends AbstractFormOption
{
    use TSelectOptionsFormOption;

    #[\Override]
    public function getId(): string
    {
        return 'checkboxes';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        $formField = MultipleSelectionFormField::create($id);
        $this->setSelectOptions($formField, $configuration);

        return $formField;
    }

    #[\Override]
    public function getConfigurationFormFields(): array
    {
        return ['selectOptions', 'required'];
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new MultipleSelectionFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return $this->getFormatter();
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return \implode(',', $value);
    }

    /**
     * @return array<string|int>
     */
    #[\Override]
    public function unserializeValue(string $value): array
    {
        return \explode(',', $value);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $columnName, mixed $value): void
    {
        foreach ($this->unserializeValue($value) as $selectedValue) {
            $list->getConditionBuilder()->add(
                "({$columnName} = ? OR {$columnName} LIKE ? OR {$columnName} LIKE ? OR {$columnName} LIKE ?) ",
                [
                    $selectedValue,
                    WCF::getDB()->escapeLikeValue($selectedValue) . ',%',
                    '%,' . WCF::getDB()->escapeLikeValue($selectedValue),
                    '%,' . WCF::getDB()->escapeLikeValue($selectedValue) . ',%',
                ]
            );
        }
    }
}
