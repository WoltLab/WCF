<?php
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
// enforce new limits for session timeout (prevents misconfiguration)
$sql = "SELECT	optionID, optionValue
	FROM	wcf".WCF_N."_option
	WHERE	optionName = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array('session_timeout'));
$row = $statement->fetchArray();

$sql = "UPDATE	wcf".WCF_N."_option
	SET	optionValue = ?
	WHERE	optionID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(
	min(max(600, $row['optionValue']), 86400),
	$row['optionID']
));
