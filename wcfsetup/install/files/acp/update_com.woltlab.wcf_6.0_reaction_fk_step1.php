<?php

namespace wcf\acp;

/**
 * Remove extraneous foreign keys on `wcf1_like.reactionTypeID` that use generic
 * `*_ibfk` names
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

$databaseEditor = WCF::getDB()->getEditor();
$tableName = 'wcf' . WCF_N . '_like';
foreach ($databaseEditor->getForeignKeys($tableName) as $foreignKey => $columnData) {
    if ($columnData['columns'] !== ['reactionTypeID']) {
        continue;
    }

    if ($columnData['referencedColumns'] !== ['reactionTypeID']) {
        continue;
    }

    if ($columnData['referencedTable'] !== 'wcf' . WCF_N . '_reaction_type') {
        continue;
    }

    $databaseEditor->dropForeignKey($tableName, $foreignKey);

    $sql = "DELETE FROM wcf1_package_installation_sql_log
            WHERE       sqlTable = ?
                    AND sqlIndex = ?";
    $statement = WCF::getDB()->prepare($sql);
    $statement->execute([
        $tableName,
        $foreignKey,
    ]);
}
