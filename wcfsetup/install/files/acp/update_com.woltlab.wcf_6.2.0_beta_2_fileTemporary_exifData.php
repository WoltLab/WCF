<?php

namespace wcf\acp;

use wcf\system\package\SplitNodeException;
use wcf\system\WCF;
use wcf\util\JSON;

$sql = "UPDATE  wcf1_file_temporary
        SET     exifData = ?
        WHERE   identifier = ?";
$updateStatement = WCF::getDB()->prepare($sql);

WCF::getDB()->beginTransaction();

$sql = "SELECT      identifier, exifData
        FROM        wcf1_file_temporary
        WHERE       exifData LIKE ?
        ORDER BY    identifier
        FOR UPDATE";
$statement = WCF::getDB()->prepare($sql, 50);
$statement->execute(['{"%']);

$replacedData = false;
while ($row = $statement->fetchArray()) {
    // This will fail loud but this is only a beta release thus we rather
    // catch errors than discarding them silently.
    $data = JSON::decode($row['exifData']);

    $updateStatement->execute([
        \serialize($data),
        $row['identifier'],
    ]);

    $replacedData = true;
}

WCF::getDB()->commitTransaction();

if ($replacedData) {
    throw new SplitNodeException();
}
