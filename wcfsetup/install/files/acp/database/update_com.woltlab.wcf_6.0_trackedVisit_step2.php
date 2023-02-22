<?php

/**
 * Adjusts the visit tracker keys.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_tracked_visit')
        ->indices([
            DatabaseTableIndex::create('visitTime')
                ->columns([
                    'visitTime',
                ]),
            DatabaseTableIndex::create('userID_objectTypeID_objectID')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns([
                    'userID',
                    'objectTypeID',
                    'objectID',
                ]),
            DatabaseTableIndex::create('')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns([
                    'objectTypeID',
                    'objectID',
                    'userID',
                ])
                ->drop(),
            DatabaseTableIndex::create('')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns([
                    'userID',
                    'visitTime',
                ])
                ->drop(),
            DatabaseTableIndex::create('objectTypeID')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns([
                    'objectTypeID',
                    'objectID',
                    'userID',
                ])
                ->drop(),
            DatabaseTableIndex::create('userID')
                ->columns([
                    'userID',
                    'visitTime',
                ])
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_tracked_visit_type')
        ->indices([
            DatabaseTableIndex::create('visitTime')
                ->columns([
                    'visitTime',
                ]),
            DatabaseTableIndex::create('userID_objectTypeID')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns([
                    'userID',
                    'objectTypeID',
                ]),
            DatabaseTableIndex::create('')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns([
                    'objectTypeID',
                    'userID',
                ])
                ->drop(),
            DatabaseTableIndex::create('')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns([
                    'userID',
                    'visitTime',
                ])
                ->drop(),
            DatabaseTableIndex::create('objectTypeID')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns([
                    'objectTypeID',
                    'userID',
                ])
                ->drop(),
            DatabaseTableIndex::create('userID')
                ->columns([
                    'userID',
                    'visitTime',
                ])
                ->drop(),
        ]),
];
