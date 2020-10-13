<?php
namespace wcf\acp;
use wcf\system\WCF;

/**
 * Checks for any duplicate usernames and aborts the upgrade if any are found. Afterwards a basic
 * check is executed to validate if the database encoding was properly converted following an
 * upgrade to WoltLab Suite 3.0.
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

// Verify that the database encoding has been converted before upgrading to 5.3, since we removed this
// now obsolete worker with this version.
$sql = "SHOW FULL COLUMNS FROM wcf".WCF_N."_user_storage";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();

$requireConversion = true;
while ($row = $statement->fetchArray()) {
	if ($row['Field'] === 'field') {
		if (preg_match('~^utf8mb4~', $row['Collation'])) {
			$requireConversion = false;
		}
	}
}

if ($requireConversion) {
	if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
		$message = "Die Datenbanktabellen wurden noch nicht auf utf8mb4 konvertiert, dies ist für das Upgrade unbedingt erforderlich.";
	}
	else {
		$message = "The database tables have not yet been converted to utf8mb4, this is absolutely necessary for the upgrade";
	}
	
	throw new \RuntimeException($message);
}
