<?php

/**
 * Migrates the question of polls and the values of poll options into the
 * `wcf1_poll_l10n` and `wcf1_poll_option_l10n` tables. Existing polls are
 * monolingual and are stored as a single row with `languageID = NULL`.
 *
 * IMPORTANT ordering constraints for package.xml:
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step1.php`
 *   (creating the `*_l10n` tables) must run BEFORE this script.
 * - The database script `acp/database/update_com.woltlab.wcf_6.3_step3.php`
 *   (dropping the `question` and `optionValue` columns) must run AFTER this script.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

// This script owns the tables' content at this point (idempotency on re-runs).
WCF::getDB()->prepare("DELETE FROM wcf1_poll_l10n")->execute();
WCF::getDB()->prepare("DELETE FROM wcf1_poll_option_l10n")->execute();

$sql = "INSERT INTO wcf1_poll_l10n
                    (pollID, languageID, question)
        SELECT      pollID, NULL, COALESCE(question, '')
        FROM        wcf1_poll";
WCF::getDB()->prepare($sql)->execute();

$sql = "INSERT INTO wcf1_poll_option_l10n
                    (optionID, languageID, optionValue)
        SELECT      optionID, NULL, optionValue
        FROM        wcf1_poll_option";
WCF::getDB()->prepare($sql)->execute();
