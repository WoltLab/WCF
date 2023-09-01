<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `double` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class DoubleDatabaseTableColumn extends AbstractDecimalDatabaseTableColumn
{
    /**
     * @inheritDoc
     */
    protected string $type = 'double';

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMaximumDecimals(): int
    {
        return 30;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMaximumLength(): int
    {
        return 255;
    }
}
