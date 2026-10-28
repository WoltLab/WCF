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
            SmallintDatabaseTableColumn::create('type')
                ->notNull()
                ->defaultValue(1),
            IntDatabaseTableColumn::create('iconFileID'),
            VarcharDatabaseTableColumn::create('borderColor')
                ->length(255),
            JsonDatabaseTableColumn::create('conditions'),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['iconFileID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('SET NULL')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_trophy_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('trophyID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('title')
                ->length(255),
            MediumtextDatabaseTableColumn::create('description'),
        ])
        ->indices([
            DatabaseTableIndex::create('trophyID')
                ->columns(['trophyID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['trophyID'])
                ->referencedTable('wcf1_trophy')
                ->referencedColumns(['trophyID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_user_trophy_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('userTrophyID'),
            IntDatabaseTableColumn::create('languageID'),
            MediumtextDatabaseTableColumn::create('description'),
        ])
        ->indices([
            DatabaseTableIndex::create('userTrophyID')
                ->columns(['userTrophyID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['userTrophyID'])
                ->referencedTable('wcf1_user_trophy')
                ->referencedColumns(['userTrophyID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    PartialDatabaseTable::create('wcf1_media')
        ->columns([
            IntDatabaseTableColumn::create('fileID'),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['fileID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('SET NULL')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_media_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('mediaID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('title')
                ->length(255),
            MediumtextDatabaseTableColumn::create('caption'),
            VarcharDatabaseTableColumn::create('altText')
                ->length(255),
        ])
        ->indices([
            DatabaseTableIndex::create('mediaID')
                ->columns(['mediaID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['mediaID'])
                ->referencedTable('wcf1_media')
                ->referencedColumns(['mediaID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    PartialDatabaseTable::create('wcf1_poll')
        ->columns([
            // `NULL` represents a standalone poll that is embedded via bbcode.
            IntDatabaseTableColumn::create('objectTypeID'),
            IntDatabaseTableColumn::create('userID'),
            NotNullVarchar255DatabaseTableColumn::create('username')
                ->defaultValue(''),
            DefaultFalseBooleanDatabaseTableColumn::create('isMultilingual'),
            DefaultFalseBooleanDatabaseTableColumn::create('resultsRequireEnd'),
            NotNullInt10DatabaseTableColumn::create('minVotes')
                ->defaultValue(1),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_poll_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('pollID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('question')
                ->length(255),
        ])
        ->indices([
            DatabaseTableIndex::create('pollID')
                ->columns(['pollID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['pollID'])
                ->referencedTable('wcf1_poll')
                ->referencedColumns(['pollID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
    DatabaseTable::create('wcf1_poll_option_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('optionID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('optionValue')
                ->length(255),
        ])
        ->indices([
            DatabaseTableIndex::create('optionID')
                ->columns(['optionID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['optionID'])
                ->referencedTable('wcf1_poll_option')
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
            MediumtextDatabaseTableColumn::create('configuration'),
            NotNullVarchar255DatabaseTableColumn::create('outputClass')
                ->defaultValue('')
                ->drop(),
            MediumtextDatabaseTableColumn::create('enableOptions')
                ->drop(),
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
    PartialDatabaseTable::create('wcf1_user_option_category')
        ->columns([
            VarcharDatabaseTableColumn::create('l10nIdentifier')
                ->length(255),
        ]),
    DatabaseTable::create('wcf1_user_option_category_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('categoryID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('title')
                ->length(255),
            TinyintDatabaseTableColumn::create('isPristine')
                ->notNull()
                ->defaultValue(1),
        ])
        ->indices([
            DatabaseTableIndex::create('categoryID')
                ->columns(['categoryID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['categoryID'])
                ->referencedTable('wcf1_user_option_category')
                ->referencedColumns(['categoryID'])
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
    PartialDatabaseTable::create('wcf1_style_variable_value')
        ->columns([
            MediumtextDatabaseTableColumn::create('authoredValue'),
            MediumtextDatabaseTableColumn::create('authoredValueDarkMode'),
        ]),
    PartialDatabaseTable::create('wcf1_user_activity_event')
        ->columns([
            IntDatabaseTableColumn::create('userID'),
            VarcharDatabaseTableColumn::create('username')
                ->length(255),
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
        ])
        ->indices([
            DatabaseTableIndex::create('packageID')
                ->columns(['lastActivityTime', 'spiderIdentifier'])
                ->drop(),
            DatabaseTableIndex::create('lastActivityTime')
                ->columns(['lastActivityTime', 'spiderIdentifier']),
            DatabaseTableIndex::create('spiderIdentifier')
                ->columns(['spiderIdentifier', 'userID']),
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
            DefaultFalseBooleanDatabaseTableColumn::create('revokeAutomatically'),
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
    PartialDatabaseTable::create('wcf1_attachment')
        ->indices([
            DatabaseTableIndex::create('uploadTime')
                ->columns(['uploadTime']),
        ]),
    PartialDatabaseTable::create('wcf1_file')
        ->indices([
            DatabaseTableIndex::create('objectTypeID')
                ->columns(['objectTypeID', 'mimeType']),
        ]),
];
