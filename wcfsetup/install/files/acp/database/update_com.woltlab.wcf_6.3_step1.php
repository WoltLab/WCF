<?php

/**
 * Updates the database layout during the update from 6.2 to 6.3.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_label_group')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('sortAlphabetically')
        ]),
];
