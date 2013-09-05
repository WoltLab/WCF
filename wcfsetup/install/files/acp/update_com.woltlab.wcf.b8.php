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
	WHERE	sqlTable = ?
		AND sqlColumn = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(
	'wcf'.WCF_N.'_user_group',
	'groupDescription'
));
$row = $statement->fetchArray();

if ($row['count']) {
	$sql = "DELETE FROM	wcf".WCF_N."_package_installation_sql_log
		WHERE		sqlTable = ?
				AND sqlColumn = ?";
	$statement = WCF::getDB()->prepareStatement($sql);
	$statement->execute(array(
		'wcf'.WCF_N.'_user_group',
		'groupDescription'
	));
}
else {
	WCF::getDB()->getEditor()->addColumn("wcf".WCF_N."_user_group", 'groupDescription', array('type' => 'TEXT'));
}
