<?php

use wcf\system\condition\ConditionHandler;
use wcf\system\WCF;
use wcf\util\JSON;

$exportedConditions = ConditionHandler::getInstance()->exportConditions("com.woltlab.wcf.condition.userGroupAssignment");
if ($exportedConditions === []) {
    return;
}

$sql = "UPDATE wcf1_user_group_assignment
        SET    conditions = ?,
               needMigration = ?
        WHERE  assignmentID = ?";
$statement = WCF::getDB()->prepare($sql);
foreach ($exportedConditions as $assignmentID => $conditionData) {
    $statement->execute([
        JSON::encode($conditionData),
        1,
        $assignmentID,
    ]);
}
