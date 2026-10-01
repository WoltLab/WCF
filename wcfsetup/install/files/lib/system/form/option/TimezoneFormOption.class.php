<?php

namespace wcf\system\form\option;

use wcf\system\database\table\column\AbstractDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\util\DateUtil;

/**
 * Implementation of a form option for selecting a timezone.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class TimezoneFormOption extends AbstractFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'timezone';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        $timezones = [];
        foreach (DateUtil::getAvailableTimezones() as $timezone) {
            $timezones[$timezone] = 'wcf.date.timezone.' . \str_replace('/', '.', \strtolower($timezone));
        }

        $formField = SelectFormField::create($id)
            ->options($timezones)
            ->ignoreInvalidValues();

        if (isset($timezones[\TIMEZONE])) {
            $formField->defaultValue(\TIMEZONE);
        }

        return $formField;
    }

    #[\Override]
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn
    {
        return VarcharDatabaseTableColumn::create($name)
            ->length(255);
    }
}
