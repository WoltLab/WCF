<?php

/**
 * Updates the database layout during the update from 5.5 to 5.6.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\database\table\column\TinyintDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_page')
        ->columns([
            TinyintDatabaseTableColumn::create('isLandingPage')
                ->drop(),
        ]),
];
