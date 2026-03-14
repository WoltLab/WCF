<?php

use wcf\system\WCF;

// set `cachedReactions` to `NULL` so that it is possible to change the column type to `JSON`
$sql = "UPDATE  wcf1_like_object
        SET     cachedReactions = NULL";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
