<?php

/**
 * Updates the database layout during the update from 6.2 to 6.3.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\BinaryDatabaseTableColumn;
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
use wcf\system\database\table\column\TinyintDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;
use wcf\system\database\table\DatabaseTable;
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
            VarcharDatabaseTableColumn::create('l10nIdentifier')
                ->length(255),
        ]),
    DatabaseTable::create('wcf1_user_option_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('optionID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('title')
                ->length(255),
            MediumtextDatabaseTableColumn::create('description'),
            TinyintDatabaseTableColumn::create('isPristine')
                ->notNull()
                ->defaultValue(1),
        ])
        ->indices([
            DatabaseTableIndex::create('optionID')
                ->columns(['optionID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['optionID'])
                ->referencedTable('wcf1_user_option')
                ->referencedColumns(['optionID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
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
    PartialDatabaseTable::create('wcf1_style')
        ->columns([
            IntDatabaseTableColumn::create('templateGroupID'),
        ]),
    PartialDatabaseTable::create('wcf1_acp_session_log')
        ->columns([
            BinaryDatabaseTableColumn::create('sessionID')
                ->notNull()
                ->length(40),
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
    PartialDatabaseTable::create('wcf1_session')
        ->columns([
            BinaryDatabaseTableColumn::create('sessionID')
                ->notNull()
                ->length(40),
        ]),
    PartialDatabaseTable::create('wcf1_user_session')
        ->columns([
            BinaryDatabaseTableColumn::create('sessionID')
                ->notNull()
                ->length(40),
        ]),
    PartialDatabaseTable::create('wcf1_reaction_type')
        ->columns([
            IntDatabaseTableColumn::create('iconFileID'),
            VarcharDatabaseTableColumn::create('l10nIdentifier')
                ->length(255),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['iconFileID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('SET NULL')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_reaction_type_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('reactionTypeID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('title')
                ->length(255),
            TinyintDatabaseTableColumn::create('isPristine')
                ->notNull()
                ->defaultValue(1),
        ])
        ->indices([
            DatabaseTableIndex::create('reactionTypeID')
                ->columns(['reactionTypeID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['reactionTypeID'])
                ->referencedTable('wcf1_reaction_type')
                ->referencedColumns(['reactionTypeID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    PartialDatabaseTable::create('wcf1_user_rank')
        ->columns([
            IntDatabaseTableColumn::create('rankImageFileID'),
            VarcharDatabaseTableColumn::create('l10nIdentifier')
                ->length(255),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['rankImageFileID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('SET NULL')
                ->onUpdate('NO ACTION'),
        ]),
    PartialDatabaseTable::create('wcf1_user_group_assignment')
        ->columns([
            JsonDatabaseTableColumn::create('conditions'),
        ]),
    PartialDatabaseTable::create('wcf1_notice')
        ->columns([
            JsonDatabaseTableColumn::create('conditions'),
        ]),
    PartialDatabaseTable::create('wcf1_ad')
        ->columns([
            JsonDatabaseTableColumn::create('conditions'),
        ]),
    DatabaseTable::create('wcf1_captcha_question_token')
        ->columns([
            BinaryDatabaseTableColumn::create('nonce')
                ->notNull()
                ->length(16),
            NotNullInt10DatabaseTableColumn::create('expires'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['nonce']),
            DatabaseTableIndex::create('expires')
                ->columns(['expires']),
        ]),
    DatabaseTable::create('wcf1_file_uploader_token')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('fileID'),
            BinaryDatabaseTableColumn::create('tokenHash')
                ->length(16),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['fileID']),
            DatabaseTableIndex::create('tokenHash')
                ->columns(['tokenHash']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['fileID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_user_rank_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('rankID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('rankTitle')
                ->length(255),
            TinyintDatabaseTableColumn::create('isPristine')
                ->notNull()
                ->defaultValue(1),
        ])
        ->indices([
            DatabaseTableIndex::create('rankID')
                ->columns(['rankID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['rankID'])
                ->referencedTable('wcf1_user_rank')
                ->referencedColumns(['rankID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_cronjob_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('cronjobID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('description')
                ->length(255),
        ])
        ->indices([
            DatabaseTableIndex::create('cronjobID')
                ->columns(['cronjobID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['cronjobID'])
                ->referencedTable('wcf1_cronjob')
                ->referencedColumns(['cronjobID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_label_group_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('groupID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('groupName')
                ->length(80),
        ])
        ->indices([
            DatabaseTableIndex::create('groupID')
                ->columns(['groupID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['groupID'])
                ->referencedTable('wcf1_label_group')
                ->referencedColumns(['groupID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    PartialDatabaseTable::create('wcf1_contact_recipient')
        ->columns([
            VarcharDatabaseTableColumn::create('l10nIdentifier')
                ->length(255),
        ]),
    DatabaseTable::create('wcf1_contact_recipient_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('recipientID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('name')
                ->length(255),
            VarcharDatabaseTableColumn::create('email')
                ->length(255),
            TinyintDatabaseTableColumn::create('isPristine')
                ->notNull()
                ->defaultValue(1),
        ])
        ->indices([
            DatabaseTableIndex::create('recipientID')
                ->columns(['recipientID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['recipientID'])
                ->referencedTable('wcf1_contact_recipient')
                ->referencedColumns(['recipientID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_paid_subscription_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('subscriptionID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('title')
                ->length(255),
            TextDatabaseTableColumn::create('description'),
        ])
        ->indices([
            DatabaseTableIndex::create('subscriptionID')
                ->columns(['subscriptionID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['subscriptionID'])
                ->referencedTable('wcf1_paid_subscription')
                ->referencedColumns(['subscriptionID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    PartialDatabaseTable::create('wcf1_language_item')
        ->indices([
            DatabaseTableIndex::create('languageCustomItemDisableTime')
                ->columns(['languageCustomItemDisableTime']),
        ]),
];
