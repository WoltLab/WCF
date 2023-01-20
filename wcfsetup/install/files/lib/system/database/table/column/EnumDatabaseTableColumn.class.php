<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `enum` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class EnumDatabaseTableColumn extends AbstractDatabaseTableColumn implements
    IDefaultValueDatabaseTableColumn,
    IEnumDatabaseTableColumn
{
    use TDefaultValueDatabaseTableColumn;
    use TEnumDatabaseTableColumn;

    /**
     * @inheritDoc
     */
    protected string $type = 'enum';
}
