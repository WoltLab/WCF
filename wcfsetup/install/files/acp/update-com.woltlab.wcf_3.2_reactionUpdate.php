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
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

OptionEditor::import([
	'like_show_summary' => 1,
	'like_enable_dislike' => 0
]);

try {
	WCF::getDB()->beginTransaction();
	
	$reactionTypes = <<<DATA
('wcf.reactionType.title1', 1, 'like.svg'), 
('wcf.reactionType.title2', 2, 'haha.svg'), 
('wcf.reactionType.title3', 3, 'sad.svg'),
('wcf.reactionType.title4', 4, 'confused.svg'),
('wcf.reactionType.title5', 5, 'thanks.svg'),
DATA;
	
	// add reaction columns 
	$statement = WCF::getDB()->prepareStatement('INSERT INTO wcf'.WCF_N.'_reaction_type (title, showOrder, iconFile) VALUES '. $reactionTypes);
	$statement->execute();
	
	// update current likes 
	$sql = "UPDATE wcf".WCF_N."_like SET reactionTypeID = ? WHERE likeValue = ?";
	$statement = WCF::getDB()->prepareStatement($sql);
	$statement->execute([
		Like::LIKE,
		1
	]);
	
	// Delete outdated or unsupported likes.
	WCF::getDB()->prepareStatement("DELETE FROM wcf".WCF_N."_like WHERE reactionTypeID = 0")->execute();
	
	// add foreign key  
	$statement = WCF::getDB()->prepareStatement('ALTER TABLE wcf'.WCF_N.'_like ADD FOREIGN KEY (reactionTypeID) REFERENCES wcf1_reaction_type (reactionTypeID) ON DELETE CASCADE');
	$statement->execute();
	
	WCF::getDB()->commitTransaction();
}
catch (Exception $e) {
	WCF::getDB()->rollBackTransaction();
	
	throw $e;
}
