<?php

namespace wcf\system\form\option;

use wcf\system\database\table\column\AbstractDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\NumericRangeFormField;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\system\form\option\formatter\IntegerFormatter;
use wcf\util\StringUtil;

/**
 * Implementation of a form field for integer values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class IntegerFormOption extends AbstractNumericFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'integer';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        $formField = IntegerFormField::create($id);
        if (isset($configuration['minIntegerValue'])) {
            $formField->minimum($configuration['minIntegerValue']);
        }
        if (isset($configuration['maxIntegerValue'])) {
            $formField->maximum($configuration['maxIntegerValue']);
        }

        return $formField;
    }

    #[\Override]
    public function getFilterFormField(string $id, array $configuration = []): AbstractFormField
    {
        return NumericRangeFormField::create($id)
            ->nullable()
            ->integerValues();
    }

    #[\Override]
    public function getConfigurationFormFields(): array
    {
        return ['unit', 'minIntegerValue', 'maxIntegerValue', 'required'];
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new IntegerFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return $this->getFormatter();
    }

    #[\Override]
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn
    {
        return IntDatabaseTableColumn::create($name);
    }

    #[\Override]
    protected function getSuffix(array $configuration): string
    {
        if (!empty($configuration['unit'])) {
            return StringUtil::encodeHTML($configuration['unit']);
        }

        return '';
    }
}
