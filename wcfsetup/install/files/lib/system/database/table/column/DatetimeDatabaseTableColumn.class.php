<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `datetime` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class DatetimeDatabaseTableColumn extends AbstractDatabaseTableColumn implements IDefaultValueDatabaseTableColumn
{
    use TDefaultValueDatabaseTableColumn;

    /**
     * @inheritDoc
     */
    protected string $type = 'datetime';
}
