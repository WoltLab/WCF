<?php

namespace wcf\system\form\option;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SourceCodeFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\system\form\option\formatter\SourceCodeFormatter;
use wcf\system\WCF;

/**
 * Implementation of a form field for source code values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class SourceCodeFormOption extends AbstractFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'sourceCode';
    }

    #[\Override]
    public function getConfigurationFormFields(): array
    {
        return \array_merge(parent::getConfigurationFormFields(), ['sourceCodeLanguage']);
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        $formField = SourceCodeFormField::create($id);

        $language = (string)($configuration['sourceCodeLanguage'] ?? '');
        if (\in_array($language, SourceCodeFormField::LANGUAGES, true)) {
            $formField->language($language);
        }

        return $formField;
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new SourceCodeFormatter();
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
