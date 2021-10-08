<?php

/**
 * Updates the database layout during the update from 5.4 to 5.5.
 *
 * @author Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_blacklist_entry')
        ->columns([
            DatabaseTableIndex::create('lastSeen')
                ->columns(['lastSeen']),
        ]),
    PartialDatabaseTable::create('wcf1_comment')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('hasEmbeddedObjects'),
        ]),
    PartialDatabaseTable::create('wcf1_comment_response')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('hasEmbeddedObjects'),
        ]),
];
