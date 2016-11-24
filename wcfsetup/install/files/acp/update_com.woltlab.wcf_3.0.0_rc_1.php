<?php
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
$sessionID = SessionHandler::getInstance()->sessionID;

SessionHandler::getInstance()->disableUpdate();

$sql = "SELECT  *
	FROM    wcf".WCF_N."_acp_session_virtual
	WHERE   sessionID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([$sessionID]);
$sessionVariables = '';
while ($row = $statement->fetchArray()) {
	$tmp = @unserialize($row['sessionVariables']);
	if ($tmp['__SECURITY_TOKEN'] == SECURITY_TOKEN) {
		$sessionVariables = $row['sessionVariables'];
	}
}

$sql = "UPDATE  wcf".WCF_N."_acp_session
	SET     sessionVariables = ?
	WHERE   sessionID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
	$sessionVariables,
	$sessionID
]);

$statement = WCF::getDB()->prepareStatement("ALTER TABLE wcf".WCF_N."_acp_session_virtual DROP COLUMN sessionVariables");
$statement->execute();

