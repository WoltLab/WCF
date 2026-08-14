<?php

use wcf\system\WCF;

// `wcf1_style.templateGroupID` is nullable now, therefore replace `0` and dangling
// references with `NULL` before the foreign key is added.
$sql = "UPDATE  wcf1_style
        SET     templateGroupID = ?
        WHERE   templateGroupID = ?
            OR  templateGroupID NOT IN (
                    SELECT  templateGroupID
                    FROM    wcf1_template_group
                )";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([null, 0]);
