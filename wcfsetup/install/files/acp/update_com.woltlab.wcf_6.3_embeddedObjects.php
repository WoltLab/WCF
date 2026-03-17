<?php

use wcf\system\WCF;

// Remove orphaned entries associated with `messageID=0`.
$sql = "DELETE FROM wcf1_message_embedded_object WHERE messageID = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([0]);
