<?php

use wcf\system\condition\ConditionHandler;
use wcf\system\WCF;
use wcf\util\JSON;

$exportedConditions = ConditionHandler::getInstance()->exportConditions("com.woltlab.wcf.condition.notice");
if ($exportedConditions === []) {
    return;
}

$sql = "UPDATE wcf1_notice
        SET    conditions = ?,
               isLegacy = ?
        WHERE  noticeID = ?";
$statement = WCF::getDB()->prepare($sql);
foreach ($exportedConditions as $noticeID => $conditionData) {
    // TODO handle user option from `com.woltlab.wcf.user.userOptions`

    $statement->execute([
        JSON::encode($conditionData),
        1,
        $noticeID,
    ]);
}
