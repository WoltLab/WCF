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
$sql = "UPDATE	wcf".WCF_N."_option
	SET	optionValue =  MIN(MAX(optionValue, ?), ?)
	WHERE	optionName = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(
	600,
	86400,
	'session_timeout'
));
