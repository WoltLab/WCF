<?php

/**
 * Updates the database layout during the update from 6.2 to 6.3.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
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
    PartialDatabaseTable::create('wcf1_contact_recipient')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('name')
                ->drop(),
            NotNullVarchar255DatabaseTableColumn::create('email')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_paid_subscription')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('title')
                ->defaultValue('')
                ->drop(),
            TextDatabaseTableColumn::create('description')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_trophy')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('title')
                ->drop(),
            MediumtextDatabaseTableColumn::create('description')
                ->drop(),
            MediumtextDatabaseTableColumn::create('iconFile')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_user_trophy')
        ->columns([
            MediumtextDatabaseTableColumn::create('description')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_captcha_question')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('question')
                ->drop(),
            MediumtextDatabaseTableColumn::create('answers')
                ->drop(),
        ]),
    // The localized content is migrated to `wcf1_media_l10n`.
    DatabaseTable::create('wcf1_media_content')
        ->drop(),
    // The question and option values are migrated to `wcf1_poll_l10n` and `wcf1_poll_option_l10n`.
    PartialDatabaseTable::create('wcf1_poll')
        ->columns([
            VarcharDatabaseTableColumn::create('question')
                ->length(255)
                ->defaultValue('')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_poll_option')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('optionValue')
                ->defaultValue('')
                ->drop(),
        ]),
    // The link pattern is migrated to the configuration of the user options.
    PartialDatabaseTable::create('wcf1_user_option')
        ->columns([
            MediumtextDatabaseTableColumn::create('labeledUrl')
                ->drop(),
            MediumtextDatabaseTableColumn::create('additionalData')
                ->drop(),
        ]),
];
