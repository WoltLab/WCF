<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `float` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class FloatDatabaseTableColumn extends AbstractDecimalDatabaseTableColumn
{
    /**
     * @inheritDoc
     */
    protected string $type = 'float';

    /**
     * @inheritDoc
     */
    public function getMaximumDecimals(): int
    {
        return 30;
    }

    /**
     * @inheritDoc
     */
    public function getMaximumLength(): int
    {
        return 255;
    }
}
