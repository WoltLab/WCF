<?php

/**
 * Deletes duplicate entries in the message_embedded_object table.
 *
 * @author  Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

WCF::getDB()->beginTransaction();

$sql = "SELECT   messageObjectTypeID, messageID, embeddedObjectTypeID, embeddedObjectID, COUNT(*) as counter
        FROM     wcf1_message_embedded_object
        GROUP BY messageObjectTypeID, messageID, embeddedObjectTypeID, embeddedObjectID
        HAVING   counter > 1";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();

$sql = "DELETE FROM wcf1_message_embedded_object
        WHERE       messageObjectTypeID = ?
        AND         messageID = ?
        AND         embeddedObjectTypeID = ?
        AND         embeddedObjectID = ?
        LIMIT       ?";
$deleteStatement = WCF::getDB()->prepare($sql);

while ($row = $statement->fetchArray()) {
    $deleteStatement->execute([
        $row['messageObjectTypeID'],
        $row['messageID'],
        $row['embeddedObjectTypeID'],
        $row['embeddedObjectID'],
        $row['counter'] - 1
    ]);
}

WCF::getDB()->commitTransaction();
