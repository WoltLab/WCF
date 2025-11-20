<?php

namespace wcf\system\form\option;

use wcf\data\DatabaseObjectList;
use wcf\system\database\table\column\AbstractDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\EmailFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\option\formatter\EmailFormatter;
use wcf\system\form\option\formatter\IFormOptionFormatter;
use wcf\system\WCF;

/**
 * Implementation of a form field for email addresses.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class EmailFormOption extends AbstractFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'email';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        return EmailFormField::create($id);
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new EmailFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return $this->getFormatter();
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

    #[\Override]
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn
    {
        return VarcharDatabaseTableColumn::create($name)
            ->length(255);
    }
}
