<?php
use wcf\data\like\Like;
use wcf\data\option\OptionEditor;
use wcf\system\WCF;

// !!!!!!!!!
// HEADS UP!    The columns for wcf1_like, wcf1_like_object and the wcf1_reaction_type table must already exists, before calling this script.
// HEADS UP!    The foreign key for the wcf1_like table will be created within this script, after providing real values for the reactionTypeID
// HEADS UP!    column. Also this script provides the basic reactionTypes.
// !!!!!!!!!

/**
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

OptionEditor::import([
	'like_show_summary' => 1,
	'like_enable_dislike' => 0
]);

try {
	WCF::getDB()->beginTransaction();
	
	// add reaction columns 
	$statement = WCF::getDB()->prepareStatement(str_replace('wcf1_', 'wcf'.WCF_N.'_', 'INSERT INTO wcf1_reaction_type (title, type, showOrder, iconFile) VALUES (\'wcf.reactionType.title1\', 1, 1, \'like.svg\'), (\'wcf.reactionType.title2\', 1, 2, \'haha.svg\'), (\'wcf.reactionType.title3\', -1, 3, \'sad.svg\'), (\'wcf.reactionType.title4\', 0, 4, \'confused.svg\'), (\'wcf.reactionType.title5\', 1, 5, \'thanks.svg\')'));
	$statement->execute();
	
	// update current likes 
	$sql = "UPDATE wcf1_like SET reactionTypeID = ? WHERE likeValue = ?";
	$statement = WCF::getDB()->prepareStatement(str_replace('wcf1_', 'wcf'.WCF_N.'_', $sql));
	
	$statement->execute([
		Like::LIKE,
		1
	]);
	$statement->execute([
		Like::DISLIKE,
		3
	]);
	
	// delete outdated likes, which aren't likes nor dislikes (normally none should exist)
	$sql = "DELETE wcf1_like WHERE reactionTypeID = 0";
	$statement = WCF::getDB()->prepareStatement(str_replace('wcf1_', 'wcf'.WCF_N.'_', $sql));
	
	// add foreign key  
	$statement = WCF::getDB()->prepareStatement(str_replace('wcf1_', 'wcf'.WCF_N.'_', 'ALTER TABLE wcf1_like ADD FOREIGN KEY (reactionTypeID) REFERENCES wcf1_reaction_type (reactionTypeID) ON DELETE CASCADE'));
	$statement->execute();
	
	WCF::getDB()->commitTransaction();
}
catch (Exception $e) {
	WCF::getDB()->rollBackTransaction();
	
	throw $e;
}
