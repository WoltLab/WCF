<?php
use wcf\data\label\group\LabelGroupList;
use wcf\data\label\LabelEditor;
use wcf\data\label\LabelList;
use wcf\data\template\group\TemplateGroupEditor;
use wcf\data\template\group\TemplateGroupList;
use wcf\util\exception\CryptoException;
use wcf\util\CryptoUtil;
use wcf\util\FileUtil;
use wcf\system\WCF;

// update label's show order
$groupList = new LabelGroupList();
$groupList->readObjects();
foreach ($groupList as $group) {
	$showOrder = 1;
	$labelList = new LabelList();
	$labelList->getConditionBuilder()->add('groupID = ?', [$group->groupID]);
	$labelList->sqlOrderBy = 'label';
	$labelList->readObjects();
	foreach ($labelList as $label) {
		$editor = new LabelEditor($label);
		$editor->update(['showOrder' => $showOrder]);
		$showOrder++;
	}
}
// If a template group uses '_wcf_email' as the folder: Move it!
$templateGroupList = new TemplateGroupList();
$templateGroupList->getConditionBuilder()->add('templateGroupFolderName = ? AND templateGroupName <> ?', ['_wcf_email/', 'wcf.acp.template.group.email']);
$templateGroupList->readObjects();
foreach ($templateGroupList as $templateGroup) {
	$i = 1;
	do {
		$newTemplateGroupFolderName = FileUtil::addTrailingSlash(FileUtil::removeTrailingSlash($templateGroup->templateGroupFolderName) . $i);
		$i++;
	}
	while (file_exists(WCF_DIR . 'templates/' . $newTemplateGroupFolderName));
	
	@rename(WCF_DIR . 'templates/' . $templateGroup->templateGroupFolderName, WCF_DIR . 'templates/' . $newTemplateGroupFolderName);
	$editor = new TemplateGroupEditor($templateGroup);
	$editor->update(['templateGroupFolderName' => $newTemplateGroupFolderName]);
}

// Fill in the mail_* constants with something sane.
$sql = "UPDATE	wcf".WCF_N."_option
	SET	optionValue = ?
	WHERE	optionName = ?";
$statement = WCF::getDB()->prepareStatement($sql);
if (!MAIL_FROM_NAME) {
	$statement->execute([WCF::getUser()->username, 'mail_from_name']);
}
if (!MAIL_FROM_ADDRESS) {
	$statement->execute([WCF::getUser()->email, 'mail_from_address']);
}
if (!MAIL_ADMIN_ADDRESS) {
	$statement->execute([WCF::getUser()->email, 'mail_admin_address']);
}

// Generate signature_secret
try {
	$statement->execute([
		bin2hex(CryptoUtil::randomBytes(20)),
		'signature_secret'
	]);
}
catch (CryptoException $e) {
	// ignore, the secret will stay empty and crypto operations
	// depending on it will fail
}

// add vortex update servers if missing
$serverURLs = [
	'http://update.woltlab.com/vortex/',
	'http://store.woltlab.com/vortex/'
];
foreach ($serverURLs as $serverURL) {
	$sql = "SELECT  COUNT(*) AS count
		FROM    wcf" . WCF_N . "_package_update_server
		WHERE   serverURL = ?";
	$statement = WCF::getDB()->prepareStatement($sql);
	$statement->execute([$serverURL]);
	if (!$statement->fetchColumn()) {
		$sql = "INSERT INTO     wcf" . WCF_N . "_package_update_server
					(serverURL)
			VALUES          (?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$serverURL]);
	}
}

// set default landing page
$sql = "UPDATE	wcf".WCF_N."_page
	SET	isLandingPage = ?
	WHERE	identifier = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
	1,
	'com.woltlab.wcf.Dashboard'
]);
