<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `smallint` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class SmallintDatabaseTableColumn extends AbstractIntDatabaseTableColumn
{
    /**
     * @inheritDoc
     */
    protected string $type = 'smallint';

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMaximumLength(): int
    {
        return 6;
    }
}
