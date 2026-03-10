<?php

/**
 * Updates the database layout during the update from 6.2 to 6.3.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\JsonDatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\SmallintDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_label_group')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('sortAlphabetically')
        ]),
    PartialDatabaseTable::create('wcf1_trophy')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('title'),
            SmallintDatabaseTableColumn::create('type')
                ->notNull()
                ->defaultValue(1),
        ]),
    PartialDatabaseTable::create('wcf1_like_object')
        ->columns([
            JsonDatabaseTableColumn::create('cachedReactions'),
        ])
];
