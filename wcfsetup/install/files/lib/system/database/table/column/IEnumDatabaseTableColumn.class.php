<?php

namespace wcf\system\database\table\column;

/**
 * Every database table column that supports specifying a predetermined set of valid values must
 * implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IEnumDatabaseTableColumn extends IDatabaseTableColumn
{
    /**
     * Sets the predetermined set of valid values for the database table column and returns this
     * column.
     *
     * @param string[] $values
     * @return  $this
     */
    public function enumValues(array $values): static;

    /**
     * Returns the predetermined set of valid values for the database table column.
     *
     * @return string[]
     */
    public function getEnumValues(): array;
}
