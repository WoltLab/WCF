<?php

/**
 * Updates the database layout during the update from 6.2 to 6.3.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_reaction_type')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('iconFile')
                ->defaultValue('')
                ->drop(),
            NotNullVarchar255DatabaseTableColumn::create('title')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_user_rank')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('rankImage')
                ->defaultValue('')
                ->drop(),
            NotNullVarchar255DatabaseTableColumn::create('rankTitle')
                ->defaultValue('')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_cronjob')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('description')
                ->defaultValue('')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_label_group')
        ->columns([
            VarcharDatabaseTableColumn::create('groupName')
                ->notNull()
                ->length(80)
                ->drop(),
        ]),
];
