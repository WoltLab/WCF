<?php

/**
 * Drops the captcha question columns that have been migrated into the
 * `wcf1_captcha_question_l10n` table.
 *
 * IMPORTANT ordering constraint for package.xml: This script must run AFTER
 * the data migration in `acp/update_com.woltlab.wcf_6.3_captchaQuestionL10n.php`.
 */

use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_captcha_question')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('question')->drop(),
            MediumtextDatabaseTableColumn::create('answers')->drop(),
        ]),
];
