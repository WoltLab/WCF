<?php

namespace wcf\system\form\option;

use wcf\system\database\table\column\AbstractDatabaseTableColumn;
use wcf\system\database\table\column\FloatDatabaseTableColumn;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\FloatFormField;
use wcf\system\form\option\formatter\FloatFormatter;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\util\StringUtil;

/**
 * Implementation of a form field for float values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class FloatFormOption extends AbstractNumericFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'float';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        $formField = FloatFormField::create($id);
        if (isset($configuration['minValue'])) {
            $formField->minimum($configuration['minValue']);
        }
        if (isset($configuration['maxValue'])) {
            $formField->maximum($configuration['maxValue']);
        }

        return $formField;
    }

    #[\Override]
    public function getConfigurationFormFields(): array
    {
        return ['unit', 'minFloatValue', 'maxFloatValue', 'required'];
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new FloatFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return $this->getFormatter();
    }

    #[\Override]
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn
    {
        return FloatDatabaseTableColumn::create($name);
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
