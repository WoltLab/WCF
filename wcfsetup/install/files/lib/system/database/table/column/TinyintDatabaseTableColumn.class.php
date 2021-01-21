<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `tinyint` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since   5.2
 */
class TinyintDatabaseTableColumn extends AbstractIntDatabaseTableColumn
{
    /**
     * @inheritDoc
     */
    protected $type = 'tinyint';

    /**
     * @inheritDoc
     */
    public function getMaximumLength()
    {
        return 4;
    }
}
