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
class VarbinaryDatabaseTableColumn extends AbstractDatabaseTableColumn implements ILengthDatabaseTableColumn
{
    use TLengthDatabaseTableColumn {
        TLengthDatabaseTableColumn::getLength as traitGetLength;
    }

    /**
     * @inheritDoc
     */
    protected $type = 'varbinary';

    /**
     * @inheritDoc
     */
    public function getLength()
    {
        if ($this->length === null) {
            throw new \LogicException('The length of varbinary fields must be explicitly set.');
        }

        return $this->traitGetLength();
    }

    /**
     * @inheritDoc
     */
    public function getMaximumLength()
    {
        return 65535;
    }
}
