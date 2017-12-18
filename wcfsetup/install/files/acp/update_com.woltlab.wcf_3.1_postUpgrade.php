<?php

use wcf\data\option\OptionEditor;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\cache\CacheHandler;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
// force disable of HTML bbcode for all groups
$sql = "UPDATE  wcf".WCF_N."_user_group_option_value
	SET     optionValue = ?
	WHERE   groupID = ?
		AND optionID = ?";
$updateStatement = WCF::getDB()->prepareStatement($sql);

$sql = "SELECT  *
	FROM    wcf".WCF_N."_user_group_option_value
	WHERE   optionID IN (
			SELECT  optionID
			FROM    wcf".WCF_N."_user_group_option
			WHERE   optionType = ?
		)";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(['BBCodeSelect']);

WCF::getDB()->beginTransaction();
while ($row = $statement->fetchArray()) {
	$value = $row['optionValue'];
	if (!empty($value)) $value .= ',';
	$value .= 'html';
	
	$updateStatement->execute([
		$value,
		$row['groupID'],
		$row['optionID']
	]);
}
WCF::getDB()->commitTransaction();

// flush style cache since there are some new style variables which are tried to load from cache
OptionEditor::resetCache();
CacheHandler::getInstance()->flush(StyleCacheBuilder::getInstance(), []);
StyleHandler::resetStylesheets();
