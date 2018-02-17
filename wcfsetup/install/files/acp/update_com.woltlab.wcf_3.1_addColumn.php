<?php
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Adds database columns, each row in the data section
 * below is executed in a separate request.
 * 
 * WARNING: This file is deployed early in the upgrade
 *          process, if you make any changes, please
 *          update the `files_pre_sql.tar` too!
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
$data = <<<DATA
ALTER TABLE wcf1_article ADD COLUMN isDeleted TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN hasLabels TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_article_content ADD COLUMN teaserImageID INT(10);
ALTER TABLE wcf1_bbcode_media_provider ADD COLUMN name VARCHAR(80) NOT NULL, ADD COLUMN packageID INT(10) NOT NULL, ADD COLUMN className varchar(255) NOT NULL DEFAULT '';
ALTER TABLE wcf1_box ADD COLUMN lastUpdateTime INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_comment ADD COLUMN unfilteredResponses MEDIUMINT(7) NOT NULL DEFAULT '0', ADD COLUMN unfilteredResponseIDs VARCHAR(255) NOT NULL DEFAULT '', ADD COLUMN enableHtml TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN isDisabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_comment_response ADD COLUMN enableHtml TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN isDisabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_language_item ADD COLUMN languageItemOldValue MEDIUMTEXT, ADD COLUMN languageCustomItemDisableTime INT(10);
ALTER TABLE wcf1_media ADD COLUMN categoryID INT(10);
ALTER TABLE wcf1_modification_log ADD COLUMN hidden TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE wcf1_package_update ADD COLUMN pluginStoreFileID INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_page ADD COLUMN cssClassName VARCHAR(255) NOT NULL DEFAULT '', ADD COLUMN availableDuringOfflineMode TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN allowSpidersToIndex TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN excludeFromLandingPage TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_paid_subscription_user ADD COLUMN sentExpirationNotification TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_style ADD COLUMN image2x VARCHAR(255) NOT NULL DEFAULT '', ADD COLUMN hasFavicon TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN coverPhotoExtension VARCHAR(4) NOT NULL DEFAULT '', ADD COLUMN apiVersion ENUM('3.0', '3.1') NOT NULL DEFAULT '3.0';
ALTER TABLE wcf1_user ADD COLUMN trophyPoints INT(10) NOT NULL DEFAULT 0, ADD COLUMN coverPhotoHash CHAR(40) DEFAULT NULL, ADD COLUMN coverPhotoExtension VARCHAR(4) NOT NULL DEFAULT '', ADD COLUMN disableCoverPhoto TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN disableCoverPhotoReason TEXT, ADD COLUMN disableCoverPhotoExpires INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user_rank ADD COLUMN hideTitle TINYINT(1) NOT NULL DEFAULT 0;
DATA;

$lines = explode("\n", StringUtil::trim($data));

$rebuildData = WCF::getSession()->getVar('__wcfUpdateAddColumns');
if ($rebuildData === null) {
	$rebuildData = [
		'i' => 0,
		'max' => count($lines)
	];
}

// MySQL adds a column by creating a new table in the
// background and copying over all the data afterwards.
// 
// Using a single `ALTER TABLE` to add multiple columns
// results in the same runtime, because copying the table
// is what actually takes ages.
$statement = WCF::getDB()->prepareStatement(str_replace('wcf1_', 'wcf'.WCF_N.'_', $lines[$rebuildData['i']]));
$statement->execute();

$rebuildData['i']++;

if ($rebuildData['i'] === $rebuildData['max']) {
	WCF::getSession()->unregister('__wcfUpdateAddColumns');
}
else {
	WCF::getSession()->register('__wcfUpdateAddColumns', $rebuildData);
	
	// call this script again
	throw new SplitNodeException();
}
