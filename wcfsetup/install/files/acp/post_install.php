<?php
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileAction;
use wcf\system\dashboard\DashboardHandler;
use wcf\system\WCF;

// set dashboard default values
DashboardHandler::setDefaultValues('com.woltlab.wcf.user.DashboardPage', array(
// content
'com.woltlab.wcf.user.recentActivity' => 1,
// sidebar
'com.woltlab.wcf.user.registerButton' => 1,
'com.woltlab.wcf.user.signedInAs' => 2,
'com.woltlab.wcf.user.statsSidebar' => 3
));
DashboardHandler::setDefaultValues('com.woltlab.wcf.user.MembersListPage', array(
'com.woltlab.wcf.user.newestMembers' => 1,
'com.woltlab.wcf.user.mostActiveMembers' => 2
));

// update administrator user rank and user online marking
$editor = new UserEditor(WCF::getUser());
$action = new UserProfileAction(array($editor), 'updateUserRank');
$action->executeAction();
$action = new UserProfileAction(array($editor), 'updateUserOnlineMarking');
$action->executeAction();

// set default mod permissions
$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_group_option_value
				(groupID, optionID, optionValue)
	SELECT			5, optionID, 1
	FROM			wcf".WCF_N."_user_group_option
	WHERE			optionName LIKE 'mod.%'";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();
$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_group_option_value
				(groupID, optionID, optionValue)
	SELECT			6, optionID, 1
	FROM			wcf".WCF_N."_user_group_option
	WHERE			optionName LIKE 'mod.%'";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();
