<?php
use wcf\system\WCF;
use wcf\system\package\SplitNodeException;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
$rebuildData = WCF::getSession()->getVar('__wcfUpdateRebuildComments');
if ($rebuildData === null) {
	$sql = "SELECT	COUNT(*) AS count
		FROM	wcf".WCF_N."_comment
		WHERE	responses > ?";
	$statement = WCF::getDB()->prepareStatement($sql);
	$statement->execute(array(3));
	$row = $statement->fetchSingleRow();
	
	$rebuildData = array(
		'i' => 0,
		'max' => 0
	);
	
	if ($row['count']) {
		$rebuildData['max'] = ceil($row['count'] / 50);
	}
}

if ($rebuildData['max']) {
	$offset = $rebuildData['i'] * 50;
	
	// get comments
	$sql = "SELECT		commentID
		FROM		wcf".WCF_N."_comment
		WHERE		responses > ?
		ORDER BY	commentID";
	$statement = WCF::getDB()->prepareStatement($sql, 50, $offset);
	$statement->execute(array(3));
	
	$commentData = array();
	while ($row = $statement->fetchArray()) {
		$commentData[$row['commentID']] = array();
	}
	
	if (empty($commentData)) {
		WCF::getSession()->unregister('__wcfUpdateRebuildComments');
	}
	else {
		// get responses per comment
		$sql = "SELECT		responseID
			FROM		wcf".WCF_N."_comment_response
			WHERE		commentID = ?
			ORDER BY	time";
		$statement = WCF::getDB()->prepareStatement($sql, 5);
		
		foreach ($commentData as $commentID => &$responseIDs) {
			$statement->execute(array($commentID));
			while ($row = $statement->fetchArray()) {
				$responseIDs[] = $row['responseID'];
			}
			
			$responseIDs = serialize($responseIDs);
		}
		
		// set responseIDs per comment
		$sql = "UPDATE	wcf".WCF_N."_comment
			SET	responseIDs = ?
			WHERE	commentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($commentData as $commentID => $responseIDs) {
			$statement->execute(array(
				$responseIDs,
				$commentID
			));
		}
		WCF::getDB()->commitTransaction();
		
		$rebuildData['i']++;
		WCF::getSession()->register('__wcfUpdateRebuildComments', $rebuildData);
		
		// call this script again
		throw new SplitNodeException();
	}
}

