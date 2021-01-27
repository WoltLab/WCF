<?php
namespace wcf\acp;
use wcf\data\package\update\server\PackageUpdateServerEditor;
use wcf\system\WCF;

// Do not use the `PackageUpdateServer` classes because we need to access
// the raw server URL that is implicitly rewritten in 5.3.
$sql = "SELECT  *
	FROM    wcf" . WCF_N . "_package_update_server
	WHERE	LOWER(serverURL) REGEXP 'https?://(store|update)\.woltlab\.com/.*'";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();

$deleteServerIDs = [];
while ($row = $statement->fetchArray()) {
	if (!preg_match("~^https?://(?:store|update)\.woltlab\.com/5\.3/~", $row["serverURL"])) {
		$deleteServerIDs[] = $row["packageUpdateServerID"];
	}
}

if (!empty($deleteServerIDs)) {
	PackageUpdateServerEditor::deleteAll($deleteServerIDs);
}
