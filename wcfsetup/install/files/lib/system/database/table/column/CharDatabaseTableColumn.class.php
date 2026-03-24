<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `char` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class CharDatabaseTableColumn extends AbstractDatabaseTableColumn implements
    IDefaultValueDatabaseTableColumn,
    ILengthDatabaseTableColumn
{
    use TDefaultValueDatabaseTableColumn;
    use TLengthDatabaseTableColumn;

    /**
     * @inheritDoc
     */
    protected string $type = 'char';

    public function getMaximumLength(): int
    {
        return 255;
    }

    public function getMinimumLength(): int
    {
        return 1;
    }
}
