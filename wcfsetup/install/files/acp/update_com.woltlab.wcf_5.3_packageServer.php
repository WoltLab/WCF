<?php
namespace wcf\acp;
use wcf\data\package\update\server\PackageUpdateServerEditor;
use wcf\system\WCF;

// Do not use the `PackageUpdateServer` classes because we need to access
// the raw server URL that is implicitly rewritten in 5.3.
$sql = "SELECT  *
	FROM    wcf1_package_update_server
	WHERE	LOWER(serverURL) REGEXP 'https?://(store|update)\.woltlab\.com/.*'";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();

// Try to extract authentication credentials from the previous package servers.
$newServers = [
	"update" => [
		"loginUsername" => "",
		"loginPassword" => "",
	],
	"store" => [
		"loginUsername" => "",
		"loginPassword" => "",
	],
];

$deleteServerIDs = [];
while ($row = $statement->fetchArray()) {
	$deleteServerIDs[] = $row["packageUpdateServerID"];
	
	// Only use values from the "2019" servers to avoid dealing with outdated
	// credentials that have never been updated.
	$serverURL = $row["serverURL"];
	if (preg_match("~^https?://update\.woltlab\.com/2019/~", $serverURL)) {
		$newServers["update"]["loginUsername"] = $row["loginUsername"];
		$newServers["update"]["loginPassword"] = $row["loginPassword"];
	}
	else if (preg_match("~^https?://store\.woltlab\.com/2019/~", $serverURL)) {
		$newServers["store"]["loginUsername"] = $row["loginUsername"];
		$newServers["store"]["loginPassword"] = $row["loginPassword"];
	}
} 

if (!empty($deleteServerIDs)) {
	PackageUpdateServerEditor::deleteAll($deleteServerIDs);
}

// Add the new package servers.
$sql = "INSERT INTO wcf" . WCF_N . "_package_update_server (serverURL, loginUsername, loginPassword) VALUES (?, ?, ?)";
$statement = WCF::getDB()->prepareStatement($sql);
foreach ($newServers as $server => $authData) {
	$statement->execute([
		"https://{$server}.woltlab.com/5.3/",
		$authData["loginUsername"],
		$authData["loginPassword"],
	]);
} 
