<?php
use wcf\data\condition\ConditionEditor;
use wcf\data\condition\ConditionList;

/**
 * Rewrites the condition data from userTrophy to userTrophyIDs to make it more consistent.
 * The rewrite is only needed if the Core is updated from 3.1.0 Beta 1 to 3.1.0 Beta 2.
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
$conditionList = new ConditionList();
$conditionList->readObjects();

foreach ($conditionList->getObjects() as $condition) {
	if ($condition->userTrophy !== null || $condition->notUserTrophy !== null) {
		$conditionData = $condition->conditionData;
		
		if (isset($conditionData['userTrophy'])) {
			$conditionData['userTrophyIDs'] = $conditionData['userTrophy'];
			unset($conditionData['userTrophy']);
		}
		
		if (isset($conditionData['notUserTrophy'])) {
			$conditionData['notUserTrophyIDs'] = $conditionData['notUserTrophy'];
			unset($conditionData['notUserTrophy']);
		}
		
		$editor = new ConditionEditor($condition);
		$editor->update([
			'conditionData' => serialize($conditionData)
		]);
	}
}

ConditionEditor::resetCache();