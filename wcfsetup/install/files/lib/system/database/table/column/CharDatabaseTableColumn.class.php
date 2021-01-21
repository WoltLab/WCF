<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `char` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since   5.2
 */
class CharDatabaseTableColumn extends AbstractDatabaseTableColumn implements ILengthDatabaseTableColumn
{
    use TLengthDatabaseTableColumn;

    /**
     * @inheritDoc
     */
    protected $type = 'char';

    /**
     * @inheritDoc
     */
    public function getMaximumLength()
    {
        return 255;
    }

    /**
     * @inheritDoc
     */
    public function getMinimumLength()
    {
        return 1;
    }
}
