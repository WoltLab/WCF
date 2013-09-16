<?php
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
$commentUpdateData = WCF::getSession()->getVar('__commentUpdateData');
if ($commentUpdateData === null) {
	$sql = "SELECT	COUNT(*) AS count
		FROM	wcf".WCF_N."_comment";
	$statement = WCF::getDB()->prepareStatement($sql);
	$statement->execute();
	$row = $statement->fetchArray();
	$commentUpdateData = array(
		'count' => $row['count'],
		'offset' => 0
	);
}

if ($commentUpdateData['count']) {
	$sql = "SELECT		commentID
		FROM		wcf".WCF_N."_comment
		ORDER BY	commentID ASC";
	$statement = WCF::getDB()->prepareStatement($sql, 25, $commentUpdateData['offset']);
	$statement->execute();
	
	$sql = "SELECT		responseID
		FROM		wcf".WCF_N."_comment_response
		WHERE		commentID = ?
		ORDER BY	time ASC";
	$responseStatement = WCF::getDB()->prepareStatement($sql, 3);
	
	// read response ids
	$commentData = array();
	while ($row = $statement->fetchArray()) {
		$commentID = $row['commentID'];
		$responseIDs = array();
		
		$responseStatement->execute(array($commentID));
		while ($innerRow = $responseStatement->fetchArray()) {
			$responseIDs[] = $innerRow['responseID'];
		}
		$commentData[$commentID] = serialize($responseIDs);
	}
	
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
	
	$commentUpdateData['offset'] += 25;
}

WCF::getSession()->register('__commentUpdateData', serialize($commentUpdateData));

// force new execution of current node
if ($commentUpdateData['count'] >= $commentUpdateData['offset']) {
	throw new SplitNodeException();
}
