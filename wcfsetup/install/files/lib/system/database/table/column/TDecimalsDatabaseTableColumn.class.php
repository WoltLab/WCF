<?php

namespace wcf\system\database\table\column;

/**
 * Provides default implementation of the methods of `IDecimalsDatabaseTableColumn`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
trait TDecimalsDatabaseTableColumn
{
    use TLengthDatabaseTableColumn;

    /**
     * number of decimals the database table column supports
     */
    protected ?int $decimals = null;

    /**
     * Sets the number of decimals the database table column supports or unsets the previously
     * set value if `null` is passed and returns this column.
     *
     * @return  $this
     */
    public function decimals(?int $decimals): static
    {
        if ($this->getMaximumDecimals() !== null && $decimals > $this->getMaximumDecimals()) {
            throw new \InvalidArgumentException(
                "Given number of decimals is greater than the maximum number '{$this->getMaximumDecimals()}'."
            );
        }

        $this->decimals = $decimals;

        return $this;
    }

    /**
     * Returns the number of decimals the database table column supports or `null` if the number
     * of decimals has not be specified.
     */
    public function getDecimals(): ?int
    {
        return $this->decimals;
    }

    /**
     * Returns the maxium number of decimals supported by this column or `null` if there is no such
     * maximum.
     */
    public function getMaximumDecimals(): ?int
    {
        return null;
    }
}
