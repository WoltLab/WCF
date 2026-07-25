<?php

/**
 * Updates the database layout during the update from 6.2 to 6.3.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\CharDatabaseTableColumn;
use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\JsonDatabaseTableColumn;
use wcf\system\database\table\column\MediumintDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\SmallintDatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
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
            NotNullVarchar255DatabaseTableColumn::create('slug')
                ->defaultValue(''),
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
        ]),
    PartialDatabaseTable::create('wcf1_user_option')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('showOnUserCard'),
        ]),
    PartialDatabaseTable::create('wcf1_smiley')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('emoji')
                ->defaultValue(''),
        ]),
    PartialDatabaseTable::create('wcf1_user')
        ->columns([
            CharDatabaseTableColumn::create('lostPasswordKey')
                ->length(64),
        ]),
    PartialDatabaseTable::create('wcf1_acp_session_log')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('hostname')
                ->defaultValue('')
                ->drop(),
        ]),
    DatabaseTable::create('wcf1_captcha_question_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('questionID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('question')
                ->length(255),
            MediumtextDatabaseTableColumn::create('answers'),
        ])
        ->indices([
            DatabaseTableIndex::create('questionID')
                ->columns(['questionID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['questionID'])
                ->referencedTable('wcf1_captcha_question')
                ->referencedColumns(['questionID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
];
