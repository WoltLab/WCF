<?php
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */

// fix superfluous group ID in accessibleGroups list
$sql = "SELECT	groupID
	FROM	wcf".WCF_N."_user_group";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();
$validGroupIDs = array();
while ($row = $statement->fetchArray()) {
	$validGroupIDs[] = $row['groupID'];
}

$sql = "SELECT	*
	FROM	wcf".WCF_N."_user_group_option_value
	WHERE	optionID = (
			SELECT	optionID
			FROM	wcf".WCF_N."_user_group_option
			WHERE	optionName = 'admin.user.accessibleGroups'
		)";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();
$updateData = array();
$optionID = 0;
while ($row = $statement->fetchArray()) {
	$optionID = $row['optionID'];
	$groupIDs = explode(',', $row['optionValue']);
	$newGroupIDs = array();
	for ($i = 0, $length = count($groupIDs); $i < $length; $i++) {
		$groupID = $groupIDs[$i];
		if (in_array($groupID, $validGroupIDs)) {
			$newGroupIDs[] = $groupID;
		}
	}
	
	$updateData[$row['groupID']] = implode(',', $newGroupIDs);
}

$sql = "UPDATE	wcf".WCF_N."_user_group_option_value
	SET	optionValue = ?
	WHERE	groupID = ?
		AND optionID = ?";
$statement = WCF::getDB()->prepareStatement($sql);

WCF::getDB()->beginTransaction();
foreach ($updateData as $groupID => $optionValue) {
	$statement->execute(array(
		$optionValue,
		$groupID,
		$optionID
	));
}
WCF::getDB()->commitTransaction();
