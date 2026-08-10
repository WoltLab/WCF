<?php

namespace wcf\system\form\option;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\system\form\option\formatter\MultilineTextFormatter;
use wcf\system\WCF;

/**
 * Implementation of a form field for multi-line text values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class TextareaFormOption extends AbstractFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'textarea';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        $formField = MultilineTextFormField::create($id);
        $maxLength = \intval($configuration['maxLength'] ?? 0);
        if ($maxLength !== 0) {
            $formField->maximumLength($maxLength);
        }

        return $formField;
    }

    #[\Override]
    public function getConfigurationFormFields(): array
    {
        return ['maxLength', 'required'];
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new MultilineTextFormatter();
    }

    #[\Override]
    public function getFilterFormField(string $id, array $configuration = []): AbstractFormField
    {
        return TextFormField::create($id);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $columnName, mixed $value): void
    {
        $list->getConditionBuilder()->add("{$columnName} LIKE ?", ['%' . WCF::getDB()->escapeLikeValue($value) . '%']);
    }
}
