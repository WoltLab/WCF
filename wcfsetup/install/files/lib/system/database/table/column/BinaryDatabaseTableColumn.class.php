<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `binary` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class BinaryDatabaseTableColumn extends AbstractDatabaseTableColumn implements
    IDefaultValueDatabaseTableColumn,
    ILengthDatabaseTableColumn
{
    use TDefaultValueDatabaseTableColumn;
    use TLengthDatabaseTableColumn;

    /**
     * @inheritDoc
     */
    protected string $type = 'binary';

    /**
     * @inheritDoc
     */
    public function getMaximumLength(): int
    {
        return 255;
    }

    /**
     * @inheritDoc
     */
    public function getMinimumLength(): int
    {
        return 1;
    }
}
