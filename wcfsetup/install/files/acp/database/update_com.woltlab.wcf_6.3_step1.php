<?php

/**
 * Updates the database layout during the update from 6.2 to 6.3.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_user_group_assignment')
        ->columns([
            MediumtextDatabaseTableColumn::create('conditions'),
        ]),
];
