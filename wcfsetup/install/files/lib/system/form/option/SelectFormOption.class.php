<?php

namespace wcf\system\form\option;

use wcf\system\database\table\column\AbstractDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\system\form\option\formatter\SelectFormatter;

/**
 * Implementation of a form option for selecting a single value.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class SelectFormOption extends AbstractFormOption
{
    use TSelectOptionsFormOption;

    #[\Override]
    public function getId(): string
    {
        return 'select';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        $formField = SelectFormField::create($id);
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
        return new SelectFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return new SelectFormatter(false);
    }

    #[\Override]
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn
    {
        return VarcharDatabaseTableColumn::create($name)
            ->length(255);
    }
}
