<?php
use wcf\data\language\item\LanguageItemEditor;
use wcf\data\like\Like;
use wcf\data\option\OptionEditor;
use wcf\system\language\LanguageFactory;
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
]);

try {
	WCF::getDB()->beginTransaction();
	
	$reactions = ['like', 'thanks', 'haha', 'confused', 'sad'];
	if (LIKE_ENABLE_DISLIKE) {
		$reactions[] = 'thumbsDown';
		
		$sql = "SELECT  languageCategoryID
			FROM    wcf".WCF_N."_language_category
			WHERE   languageCategory = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute(['wcf.reactionType']);
		$languageCategoryID = $statement->fetchSingleColumn();
		
		// Create a custom phrase for this reaction, it needs to be "manually" added
		// because it would otherwise conflict with the next reaction created by the
		// user, *if* there are no dislikes.
		foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
			LanguageItemEditor::create([
				'languageID' => $language->languageID,
				'languageItem' => 'wcf.reactionType.title6',
				'languageItemValue' => ($language->getFixedLanguageCode() === 'de' ? 'Gefällt mir nicht' : 'Dislike'),
				'languageCategoryID' => $languageCategoryID,
				'packageID' => 1,
			]);
		}
	}
	
	$sql = "INSERT INTO     wcf".WCF_N."_reaction_type
				(reactionTypeID, title, showOrder, iconFile)
		VALUES          (?, ?, ?, ?)";
	$statement = WCF::getDB()->prepareStatement($sql);
	for ($i = 0, $length = count($reactions); $i < $length; $i++) {
		$reactionTypeID = $i + 1;
		
		$statement->execute([
			$reactionTypeID,
			"wcf.reactionType.title{$reactionTypeID}",
			$reactionTypeID,
			"{$reactions[$i]}.svg",
		]);
	}
	
	// Update the existing (dis)likes.
	$likeValues = [Like::LIKE => 1];
	if (LIKE_ENABLE_DISLIKE) $likeValues[Like::DISLIKE] = 6;
	
	$sql = "UPDATE  wcf".WCF_N."_like
		SET     reactionTypeID = ?
		WHERE   likeValue = ?";
	$statement = WCF::getDB()->prepareStatement($sql);
	foreach ($likeValues as $likeValue => $reactionTypeID) {
		$statement->execute([
			$reactionTypeID,
			$likeValue,
		]);
	}
	
	// Delete outdated or unsupported likes.
	WCF::getDB()->prepareStatement("DELETE FROM wcf".WCF_N."_like WHERE reactionTypeID = 0")->execute();
	
	// Adjust the like objects by moving all dislikes into regular likes/cumulativeLikes.
	$sql = "UPDATE  wcf".WCF_N."_like_object
		SET     likes = likes + dislikes,
			cumulativeLikes = likes,
			dislikes = 0";
	WCF::getDB()->prepareStatement($sql)->execute();
	
	$statement = WCF::getDB()->prepareStatement('ALTER TABLE wcf'.WCF_N.'_like ADD FOREIGN KEY (reactionTypeID) REFERENCES wcf'.WCF_N.'_reaction_type (reactionTypeID) ON DELETE CASCADE');
	$statement->execute();
	
	WCF::getDB()->commitTransaction();
}
catch (Exception $e) {
	WCF::getDB()->rollBackTransaction();
	
	throw $e;
}
