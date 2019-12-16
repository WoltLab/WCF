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

$recentActivityList = new \wcf\data\user\activity\event\UserActivityEventList();
$recentActivityList->getConditionBuilder()->add("objectTypeID IN (SELECT objectTypeID FROM wcf". WCF_N ."_object_type WHERE objectType LIKE '%likeable%' AND definitionID = ?)", [$definition->definitionID]);
$recentActivityList->readObjectIDs();

if (count($recentActivityList->getObjectIDs())) {
	\wcf\data\user\activity\event\UserActivityEventEditor::deleteAll($recentActivityList->getObjectIDs());
}
