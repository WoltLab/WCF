<?php

namespace wcf\system\database\table\column;

/**
 * Abstract implementation of a decimal (data) type for database table columns.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
abstract class AbstractDecimalDatabaseTableColumn extends AbstractDatabaseTableColumn implements
    IDecimalsDatabaseTableColumn,
    IDefaultValueDatabaseTableColumn
{
    use TDecimalsDatabaseTableColumn;
    use TDefaultValueDatabaseTableColumn {
        getDefaultValue as private traitGetDefaultValue;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getDefaultValue(): string|null
    {
        $defaultValue = $this->traitGetDefaultValue();
        if ($defaultValue === null) {
            return $defaultValue;
        }

        return \number_format($defaultValue, $this->getDecimals() ?? 0, '.', '');
    }
}
