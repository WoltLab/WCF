<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `varchar` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class VarcharDatabaseTableColumn extends AbstractDatabaseTableColumn implements
    IDefaultValueDatabaseTableColumn,
    ILengthDatabaseTableColumn
{
    use TDefaultValueDatabaseTableColumn;
    use TLengthDatabaseTableColumn {
        getLength as protected traitGetLength;
    }

    /**
     * @inheritDoc
     */
    protected string $type = 'varchar';

    /**
     * @inheritDoc
     */
    public function getLength(): int
    {
        if ($this->length === null) {
            throw new \LogicException('The length of varchar fields must be explicitly set.');
        }

        return $this->traitGetLength();
    }

    /**
     * @inheritDoc
     */
    public function getMaximumLength(): int
    {
        return 65535;
    }

    /**
     * @inheritDoc
     */
    public function getMinimumLength(): int
    {
        return 1;
    }
}
