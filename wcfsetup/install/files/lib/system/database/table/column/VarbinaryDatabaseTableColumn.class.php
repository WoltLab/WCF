<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `varbinary` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since   5.2
 */
final class VarbinaryDatabaseTableColumn extends AbstractDatabaseTableColumn implements
    IDefaultValueDatabaseTableColumn,
    ILengthDatabaseTableColumn
{
    use TDefaultValueDatabaseTableColumn;
    use TLengthDatabaseTableColumn {
        TLengthDatabaseTableColumn::getLength as traitGetLength;
    }

    /**
     * @inheritDoc
     */
    protected string $type = 'varbinary';

    /**
     * @inheritDoc
     */
    public function getLength(): int
    {
        if ($this->length === null) {
            throw new \LogicException('The length of varbinary fields must be explicitly set.');
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
}
