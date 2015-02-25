<?php
use wcf\system\WCF;

/**
 * Updates the wcf1_moderation_queue by converting the 'comment' field into a wcf1_comment object.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */

$sql = "SELECT	objectTypeID
	FROM	wcf".WCF_N."_object_type
	WHERE	objectType = ?
		AND definitionID = (
			SELECT	definitionID
			FROM	wcf".WCF_N."_object_type_definition
			WHERE	definitionName = ?
		)";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(
	'com.woltlab.wcf.moderation.queue',
	'com.woltlab.wcf.comment.commentableContent'
));
$row = $statement->fetchArray();

// create comments for non-empty 'comment' fields
$sql = "INSERT INTO	wcf".WCF_N."_comment
			(objectTypeID, objectID, time, userID, username, message)
	SELECT		".$row['objectTypeID'].", queueID, ".TIME_NOW.", NULL, 'Unknown', comment
	FROM		wcf".WCF_N."_moderation_queue
	WHERE		comment <> ''";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();

// set comment counter
$sql = "UPDATE	wcf".WCF_N."_moderation_queue
	SET	comments = 1
	WHERE	comment <> ''";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();

// drop comment column
WCF::getDB()->getEditor()->dropColumn('wcf'.WCF_N.'_moderation_queue', 'comment');
