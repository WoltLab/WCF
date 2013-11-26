<?php
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */

// commit d2fbb3b

// search for foreign key name and drop it
$sql = "SHOW INDEX FROM wcf".WCF_N."_user_rank";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();
while ($row = $statement->fetchArray()) {
	if ($row['Column_name'] == 'groupID' && preg_match('~_fk$~', $row['Key_name'])) {
		// drop key
		WCF::getDB()->getEditor()->dropForeignKey("wcf".WCF_N."_user_rank", $row['Key_name']);
		
		// remove key from sql log
		$sql = "DELETE FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE		sqlTable = ?
					AND sqlIndex = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			"wcf".WCF_N."_user_rank",
			$row['Key_name']
		));
		
		break;
	}
}
