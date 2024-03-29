<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `year` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class YearDatabaseTableColumn extends AbstractDatabaseTableColumn implements
    IDefaultValueDatabaseTableColumn,
    ILengthDatabaseTableColumn
{
    use TDefaultValueDatabaseTableColumn;
    use TLengthDatabaseTableColumn;

    /**
     * @inheritDoc
     */
    protected string $type = 'year';

    /**
     * @inheritDoc
     */
    protected function validateLength(int $length): void
    {
        if ($length !== 4) {
            throw new \InvalidArgumentException("Only '4' is a valid length for year columns.");
        }
    }
}
