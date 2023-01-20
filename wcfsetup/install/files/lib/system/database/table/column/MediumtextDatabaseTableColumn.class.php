<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `mediumtext` database table column.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class MediumtextDatabaseTableColumn extends AbstractDatabaseTableColumn
{
    /**
     * @inheritDoc
     */
    protected string $type = 'mediumtext';
}
