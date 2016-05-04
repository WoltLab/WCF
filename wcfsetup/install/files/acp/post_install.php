<?php
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileAction;
use wcf\system\WCF;

// set default landing page
$sql = "UPDATE	wcf".WCF_N."_page
	SET	isLandingPage = ?
	WHERE	identifier = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
	1,
	'com.woltlab.wcf.Dashboard'
]);

// update administrator user rank and user online marking
$editor = new UserEditor(WCF::getUser());
$action = new UserProfileAction([$editor], 'updateUserRank');
$action->executeAction();
$action = new UserProfileAction([$editor], 'updateUserOnlineMarking');
$action->executeAction();
