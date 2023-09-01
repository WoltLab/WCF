<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `int` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class IntDatabaseTableColumn extends AbstractIntDatabaseTableColumn
{
    /**
     * @inheritDoc
     */
    protected string $type = 'int';

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMaximumLength(): int
    {
        return 11;
    }
}
