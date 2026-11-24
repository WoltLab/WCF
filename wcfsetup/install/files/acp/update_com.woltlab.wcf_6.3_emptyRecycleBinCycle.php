<?php

/**
 * Migrates the app-specific settings for pruning the trash bin into the
 * global setting `message_empty_recycle_bin_cycle`.
 *
 * The longest configured period is used. If any app has disabled the pruning,
 * it is disabled globally to prevent the deletion of content that previously
 * would have been kept.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\option\OptionEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

$legacyOptionNames = [
    'wbb_thread_empty_recycle_bin_cycle',
    'gallery_image_empty_recycle_bin_cycle',
    'blog_entry_empty_recycle_bin_cycle',
    'filebase_file_empty_recycle_bin_cycle',
    'calendar_event_empty_recycle_bin_cycle',
];

$conditionBuilder = new PreparedStatementConditionBuilder();
$conditionBuilder->add('optionName IN (?)', [$legacyOptionNames]);

$sql = "SELECT  optionValue
        FROM    wcf1_option
        {$conditionBuilder}";
$statement = WCF::getDB()->prepare($sql);
$statement->execute($conditionBuilder->getParameters());
$values = \array_map(
    static fn($value) => (int)$value,
    $statement->fetchAll(\PDO::FETCH_COLUMN)
);

if ($values === []) {
    return;
}

$cycle = \in_array(0, $values, true) ? 0 : \max($values);

OptionEditor::import([
    'message_empty_recycle_bin_cycle' => $cycle,
]);
