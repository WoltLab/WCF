<?php
use wcf\system\WCF;

$files = [
	'lib/system/database/table/DatabaseTableUtil.class.php',
];

$sql = "DELETE FROM     wcf".WCF_N."_package_installation_file_log
	WHERE           packageID = ?
	                AND filename = ?";
$statement = WCF::getDB()->prepareStatement($sql);

foreach ($files as $file) {
	if (file_exists(WCF_DIR . $file)) {
		unlink(WCF_DIR . $file);
	}
	
	$statement->execute([
		$this->installation->getPackageID(),
		$file,
	]);
}
