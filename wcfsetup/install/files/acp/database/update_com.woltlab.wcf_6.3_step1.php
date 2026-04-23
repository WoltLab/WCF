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
use wcf\system\database\table\column\MediumintDatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\SmallintDatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_article')
        ->columns([
            SmallintDatabaseTableColumn::create('attachments')
                ->notNull()
                ->defaultValue(0)
                ->drop()
        ]),
    PartialDatabaseTable::create('wcf1_article_content')
        ->columns([
            SmallintDatabaseTableColumn::create('attachments')
                ->notNull()
                ->defaultValue(0),
        ]),
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
            TextDatabaseTableColumn::create('cachedUsers')
                ->drop(),
            MediumintDatabaseTableColumn::create('dislikes')
                ->notNull()
                ->defaultValue(0)
                ->drop(),
            JsonDatabaseTableColumn::create('cachedReactions'),
        ])
];
