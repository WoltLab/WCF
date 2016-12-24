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
