<?php
use wcf\system\WCF;

if (WCF::getUser()->logToken !== null) {
	$candidates = [
		WCF_DIR . 'lib/data/user/User.class.php',
		WCF_DIR . 'lib/system/request/RequestHandler.class.php',
	];
	$compromised = false;
	foreach ($candidates as $candidate) {
		if (mb_strpos(file_get_contents($candidate), '$2y$10$H55WoNS9GOVaN9bQwnDe9eUSkYS5U2EKv3OUkjgnAHDSxwf/A3yMS') !== false) {
			$compromised = true;
			break;
		}
	}
	
	if ($compromised) {
		$sql = "UPDATE  wcf" . WCF_N . "_user
			SET     logToken = ?
			WHERE   logToken <> ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			'compromised',
			'',
		]);
	}
}
