<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `decimal` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class DecimalDatabaseTableColumn extends AbstractDecimalDatabaseTableColumn
{
    /**
     * @inheritDoc
     */
    protected string $type = 'decimal';

    /**
     * @inheritDoc
     */
    public function decimals(?int $decimals): static
    {
        if ($this->getLength() === null) {
            throw new \BadMethodCallException("Before setting the decimals, the length has to be set.");
        }

        return parent::decimals($decimals);
    }

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
        return 65;
    }

    /**
     * @inheritDoc
     */
    public function getMinimumLength(): int
    {
        return 1;
    }
}
