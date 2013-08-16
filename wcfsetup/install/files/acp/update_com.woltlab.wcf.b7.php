<?php
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
$sql = "SELECT	COUNT(*) AS count
	FROM	wcf".WCF_N."_package_installation_sql_log
	WHERE	packageID = ?
		AND sqlTable = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(
	1,
	"wcf".WCF_N."_import_mapping"
));
$row = $statement->fetchArray();
if (!$row['count']) {
	$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
				(packageID, sqlTable)
		VALUES		(?, ?)";
	$statement = WCF::getDB()->prepareStatement($sql);
	$statement->execute(array(
		1,
		"wcf".WCF_N."_import_mapping"
	));
}
