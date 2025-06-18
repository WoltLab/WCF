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
    renameObjectTypes($conditionData);

    $statement->execute([
        JSON::encode($conditionData),
        1,
        $assignmentID,
    ]);
}

/**
 * Rename the object types so that the migration functions can handle them.
 * @see \wcf\system\condition\provider\UserConditionProvider
 *
 * @param array<string, mixed> $conditionData
 */
function renameObjectTypes(array &$conditionData): void
{
    $objectTypeMap = [
        'com.woltlab.wcf.username' => 'com.woltlab.wcf.user.username',
        'com.woltlab.wcf.email' => 'com.woltlab.wcf.user.email',
        'com.woltlab.wcf.userGroup' => 'com.woltlab.wcf.user.userGroup',
        'com.woltlab.wcf.languages' => 'com.woltlab.wcf.user.languages',
        'com.woltlab.wcf.registrationDate' => 'com.woltlab.wcf.user.registrationDate',
        'com.woltlab.wcf.registrationDateInterval' => 'com.woltlab.wcf.user.registrationDateInterval',
        'com.woltlab.wcf.avatar' => 'com.woltlab.wcf.user.avatar',
        'com.woltlab.wcf.signature' => 'com.woltlab.wcf.user.signature',
        'com.woltlab.wcf.coverPhoto' => 'com.woltlab.wcf.user.coverPhoto',
        'com.woltlab.wcf.state' => 'com.woltlab.wcf.user.state',
        'com.woltlab.wcf.activityPoints' => 'com.woltlab.wcf.user.activityPoints',
        'com.woltlab.wcf.likesReceived' => 'com.woltlab.wcf.user.likesReceived',
        // TODO 'com.woltlab.wcf.userOptions'
        'com.woltlab.wcf.userTrophyCondition' => 'com.woltlab.wcf.user.trophyCondition',
        'com.woltlab.wcf.trophyPoints' => 'com.woltlab.wcf.user.trophyPoints',
    ];

    foreach ($objectTypeMap as $currentName => $newName) {
        if (isset($conditionData[$currentName])) {
            $conditionData[$newName] = $conditionData[$currentName];
            unset($conditionData[$currentName]);
        }
    }
}
