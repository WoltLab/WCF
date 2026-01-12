<?php

use wcf\system\WCF;

// Earlier versions set this column to an empty string instead of `NULL`.
$sql = "UPDATE  wcf1_user
        SET     coverPhotoHash = ?
        WHERE   coverPhotoHash = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([null, '']);
