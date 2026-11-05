<?php

use wcf\system\WCF;

// Articles that are not flagged as updated use their publication time as the
// time of the last update.
$sql = "UPDATE  wcf1_article
        SET     lastUpdateTime = time
        WHERE   flagAsUpdated = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([0]);
