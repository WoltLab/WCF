<?php
use wcf\system\WCF;

$removeFiles = array(
	'style/like.less',
	'style/recaptcha.less',
	'style/search.less'
);

$sql = "DELETE FROM	wcf".WCF_N."_package_installation_file_log
	WHERE		packageID = ?
			AND filename = ?";
$statement = WCF::getDB()->prepareStatement($sql);
foreach ($removeFiles as $file) {
	if (file_exists(WCF_DIR.$file)) {
		@unlink(WCF_DIR.$file);
		
		$statement->execute(array(1, $file));
	}
}
