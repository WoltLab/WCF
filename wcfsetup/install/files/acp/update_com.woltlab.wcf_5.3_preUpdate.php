<?php
namespace wcf\acp;
use wcf\system\WCF;

/**
 * Checks for any duplicate usernames and aborts the upgrade if any are found.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core
 */

$sql = "SELECT          username, COUNT(username)
	FROM            wcf" . WCF_N . "_user
	GROUP BY        username
	HAVING          COUNT(username) > ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([1]);
$usernames = $statement->fetchList('username');

if (!empty($usernames)) {
	if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
		$message = "Ein oder mehr Benutzernamen sind mehrfach gegeben, diese Konten müssen zusammengeführt oder gelöscht werden: %s";
	}
	else {
		$message = "One or more usernames exist multiple times, these accounts must be merged or deleted: %s";
	}
	
	throw new \RuntimeException(sprintf($message, implode(', ', $usernames)));
}
