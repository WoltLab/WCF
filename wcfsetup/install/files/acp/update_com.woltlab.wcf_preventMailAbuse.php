<?php
namespace wcf\acp;
use wcf\system\WCF;

$sql = "UPDATE  wcf".WCF_N."_user_group_option_value
	SET     optionValue = ?
	WHERE   optionID = (
		SELECT  optionID
		FROM    wcf".WCF_N."_user_group_option
		WHERE   optionName = ?
	)";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
	0,
	'user.profile.canMail',
]);
