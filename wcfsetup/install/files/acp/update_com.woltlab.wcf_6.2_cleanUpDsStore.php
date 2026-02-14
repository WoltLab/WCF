<?php

namespace wcf\acp;

use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;

// Purges any `.DS_Store` (or derivates) files that had been accidentially
// deployed in the past. The `Installer` class was also modified to filter out
// these files in the future.

$sql = "SELECT  *
        FROM    wcf1_package_installation_file_log
        WHERE   filename LIKE ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['%.DS_Store']);

while ($row = $statement->fetchArray()) {
    $application = ApplicationHandler::getInstance()->getApplication($row['application']);
    \assert($application !== null);

    $pathname = \WCF_DIR . $application->getPackage()->packageDir . $row['filename'];
    if (\file_exists($pathname)) {
        \unlink($pathname);
    }
}

$sql = "DELETE FROM wcf1_package_installation_file_log
        WHERE       filename LIKE ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['%.DS_Store']);
