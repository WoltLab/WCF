<?php
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
$columns = WCF::getDB()->getEditor()->getColumns("wcf".WCF_N."_application");
$hasLandingPageID = false;
foreach ($columns as $column) {
	if ($column['name'] == 'landingPageID') {
		$hasLandingPageID = true;
		break;
	}
}

if (!$hasLandingPageID) {
	$statement = WCF::getDB()->prepareStatement("ALTER TABLE wcf".WCF_N."_application ADD COLUMN landingPageID INT(10) NULL");
	$statement->execute();
	
	$statement = WCF::getDB()->prepareStatement("ALTER TABLE wcf".WCF_N."_application ADD CONSTRAINT `8a7fc72db2348bc5695394ffd616cbf5_fk` FOREIGN KEY (landingPageID) REFERENCES wcf".WCF_N."_page (pageID) ON DELETE SET NULL;");
	$statement->execute();
	
	$statement = WCF::getDB()->prepareStatement("UPDATE wcf".WCF_N."_application SET landingPageID = (SELECT pageID FROM wcf".WCF_N."_page WHERE isLandingPage = 1 LIMIT 1) WHERE packageID = 1;");
	$statement->execute();
}


// remove duplicates in page_content
$sql = "DELETE FROM     wcf".WCF_N."_page_content
	WHERE           pageID = ?
			AND languageID IS NULL
	ORDER BY        pageContentID DESC
	LIMIT           ?";
$deleteStatement = WCF::getDB()->prepareStatement($sql);

$sql = "SELECT          COUNT(*) AS count, pageID
	FROM            wcf".WCF_N."_page_content
	WHERE           languageID IS NULL
	GROUP BY        pageID
	HAVING          count > 1";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();
while ($row = $statement->fetchArray()) {
	$deleteStatement->execute([$row['pageID'], $row['count'] - 1]);
}

// remove duplicates in box_content
$sql = "DELETE FROM     wcf".WCF_N."_box_content
	WHERE           boxID = ?
			AND languageID IS NULL
	ORDER BY        boxContentID DESC
	LIMIT           ?";
$deleteStatement = WCF::getDB()->prepareStatement($sql);

$sql = "SELECT          COUNT(*) AS count, boxID
	FROM            wcf".WCF_N."_box_content
	WHERE           languageID IS NULL
	GROUP BY        boxID
	HAVING          count > 1";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();
while ($row = $statement->fetchArray()) {
	$deleteStatement->execute([$row['boxID'], $row['count'] - 1]);
}