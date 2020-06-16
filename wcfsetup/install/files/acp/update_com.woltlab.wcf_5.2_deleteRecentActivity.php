<?php
/**
 * This script deletes all recent activity events for likes. Running this script is necessary after an update to version 5.2,
 * because with this version the reaction system was introduced and the events there were changed so that they contain the
 * reaction type, which is not present in previous fired events. Older events will otherwise throw an error.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

$definitionList = new \wcf\data\object\type\definition\ObjectTypeDefinitionList();
$definitionList->getConditionBuilder()->add('definitionName = ?', ['com.woltlab.wcf.user.recentActivityEvent']);
$definitionList->readObjects();
$definition = $definitionList->current();

$sql = "SELECT  objectTypeID
	FROM    wcf". WCF_N ."_object_type
	WHERE   objectType LIKE '%likeable%'
	AND     definitionID = ?";
$statement = \wcf\system\WCF::getDB()->prepareStatement($sql);
$statement->execute([$definition->definitionID]);
$objectTypeIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

$conditionBuilder = new \wcf\system\database\util\PreparedStatementConditionBuilder();
$conditionBuilder->add('objectTypeID IN (?)', [$objectTypeIDs]);

$sql = "DELETE FROM wcf". WCF_N ."_user_activity_event ".$conditionBuilder;
$statement = \wcf\system\WCF::getDB()->prepareStatement($sql);
$statement->execute($conditionBuilder->getParameters());
