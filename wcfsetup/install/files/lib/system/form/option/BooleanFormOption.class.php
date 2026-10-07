<?php

namespace wcf\system\form\option;

use wcf\system\database\table\column\AbstractDatabaseTableColumn;
use wcf\system\database\table\column\TinyintDatabaseTableColumn;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\option\formatter\BooleanFormatter;
use wcf\system\form\option\formatter\IFormOptionFormatter;

/**
 * Implementation of a form field for boolean-type values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class BooleanFormOption extends AbstractFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'boolean';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        return BooleanFormField::create($id);
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new BooleanFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return $this->getFormatter();
    }

    #[\Override]
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn
    {
        return TinyintDatabaseTableColumn::create($name);
    }

    #[\Override]
    public function matchesFilterValue(string $optionValue, string $filterValue, array $configuration = []): bool
    {
        return (int)$optionValue === (int)$filterValue;
    }
}
