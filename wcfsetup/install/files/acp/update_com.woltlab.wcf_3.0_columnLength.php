<?php
use wcf\system\exception\SystemException;
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
$data = <<<DATA
UPDATE wcf1_acl_option SET optionName = SUBSTRING(optionName, 1, 191);
ALTER TABLE wcf1_acl_option CHANGE optionName optionName VARCHAR(191) NOT NULL;
UPDATE wcf1_acl_option SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_acl_option CHANGE categoryName	categoryName VARCHAR(191) NOT NULL;
UPDATE wcf1_acl_option_category SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_acl_option_category CHANGE categoryName categoryName VARCHAR(191) NOT NULL;
UPDATE wcf1_acp_menu_item SET menuItem = SUBSTRING(menuItem, 1, 191);
ALTER TABLE wcf1_acp_menu_item CHANGE menuItem menuItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_acp_menu_item SET parentMenuItem = SUBSTRING(parentMenuItem, 1, 191);
ALTER TABLE wcf1_acp_menu_item CHANGE parentMenuItem parentMenuItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_acp_search_provider SET providerName = SUBSTRING(providerName, 1, 191);
ALTER TABLE wcf1_acp_search_provider CHANGE providerName providerName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_acp_template SET templateName = SUBSTRING(templateName, 1, 191);
ALTER TABLE wcf1_acp_template CHANGE templateName templateName VARCHAR(191) NOT NULL;
UPDATE wcf1_bbcode SET bbcodeTag = SUBSTRING(bbcodeTag, 1, 191);
ALTER TABLE wcf1_bbcode CHANGE bbcodeTag bbcodeTag VARCHAR(191) NOT NULL;
UPDATE wcf1_clipboard_action SET actionClassName = SUBSTRING(actionClassName, 1, 191);
ALTER TABLE wcf1_clipboard_action CHANGE actionClassName actionClassName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_core_object SET objectName = SUBSTRING(objectName, 1, 191);
ALTER TABLE wcf1_core_object CHANGE objectName objectName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_import_mapping SET oldID = SUBSTRING(oldID, 1, 191);
ALTER TABLE wcf1_import_mapping CHANGE oldID oldID VARCHAR(191) NOT NULL;
UPDATE wcf1_language_category SET languageCategory = SUBSTRING(languageCategory, 1, 191);
ALTER TABLE wcf1_language_category CHANGE languageCategory languageCategory VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_language_item SET languageItem = SUBSTRING(languageItem, 1, 191);
ALTER TABLE wcf1_language_item CHANGE languageItem languageItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_object_type SET objectType = SUBSTRING(objectType, 1, 191);
ALTER TABLE wcf1_object_type CHANGE objectType objectType VARCHAR(191) NOT NULL;
UPDATE wcf1_object_type_definition SET definitionName = SUBSTRING(definitionName, 1, 191);
ALTER TABLE wcf1_object_type_definition CHANGE definitionName definitionName VARCHAR(191) NOT NULL;
UPDATE wcf1_option SET optionName = SUBSTRING(optionName, 1, 191);
ALTER TABLE wcf1_option CHANGE optionName optionName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_option SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_option CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_option_category SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_option_category CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_option_category SET parentCategoryName = SUBSTRING(parentCategoryName, 1, 191);
ALTER TABLE wcf1_option_category CHANGE parentCategoryName parentCategoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package SET package = SUBSTRING(package, 1, 191);
ALTER TABLE wcf1_package CHANGE package package VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package_exclusion SET excludedPackage = SUBSTRING(excludedPackage, 1, 191);
ALTER TABLE wcf1_package_exclusion CHANGE excludedPackage excludedPackage VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package_installation_file_log SET filename = SUBSTRING(filename, 1, 191);
ALTER TABLE wcf1_package_installation_file_log CHANGE filename filename VARBINARY(765) NOT NULL;
UPDATE wcf1_package_installation_plugin SET pluginName = SUBSTRING(pluginName, 1, 191);
ALTER TABLE wcf1_package_installation_plugin CHANGE pluginName pluginName VARCHAR(191) NOT NULL;
UPDATE wcf1_package_update SET package = SUBSTRING(package, 1, 191);
ALTER TABLE wcf1_package_update CHANGE package package VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package_update_exclusion SET excludedPackage = SUBSTRING(excludedPackage, 1, 191);
ALTER TABLE wcf1_package_update_exclusion CHANGE excludedPackage excludedPackage VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_package_update_requirement SET package = SUBSTRING(package, 1, 191);
ALTER TABLE wcf1_package_update_requirement CHANGE package package VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_search_keyword SET keyword = SUBSTRING(keyword, 1, 191);
ALTER TABLE wcf1_search_keyword CHANGE keyword keyword VARCHAR(191) NOT NULL;
UPDATE wcf1_session SET userAgent = SUBSTRING(userAgent, 1, 191);
ALTER TABLE wcf1_session CHANGE userAgent userAgent VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_session_virtual SET userAgent = SUBSTRING(userAgent, 1, 191);
ALTER TABLE wcf1_session_virtual CHANGE userAgent userAgent VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_smiley SET smileyCode = SUBSTRING(smileyCode, 1, 191);
ALTER TABLE wcf1_smiley CHANGE smileyCode smileyCode VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_spider SET spiderIdentifier = SUBSTRING(spiderIdentifier, 1, 191);
ALTER TABLE wcf1_spider CHANGE spiderIdentifier spiderIdentifier VARCHAR(191) DEFAULT '';
UPDATE wcf1_tag SET name = SUBSTRING(name, 1, 191);
ALTER TABLE wcf1_tag CHANGE name name VARCHAR(191) NOT NULL;
UPDATE wcf1_template SET templateName = SUBSTRING(templateName, 1, 191);
ALTER TABLE wcf1_template CHANGE templateName templateName VARCHAR(191) NOT NULL;
UPDATE wcf1_user SET username = SUBSTRING(username, 1, 100);
ALTER TABLE wcf1_user CHANGE username username VARCHAR(100) NOT NULL DEFAULT '';
UPDATE wcf1_user SET email = SUBSTRING(email, 1, 191);
ALTER TABLE wcf1_user CHANGE email email VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user SET authData = SUBSTRING(authData, 1, 191);
ALTER TABLE wcf1_user CHANGE authData authData VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_collapsible_content SET objectID = SUBSTRING(objectID, 1, 191);
ALTER TABLE wcf1_user_collapsible_content CHANGE objectID objectID VARCHAR(191) NOT NULL;
UPDATE wcf1_user_group_option SET optionName = SUBSTRING(optionName, 1, 191);
ALTER TABLE wcf1_user_group_option CHANGE optionName optionName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_group_option SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_user_group_option CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_group_option_category SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_user_group_option_category CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_group_option_category SET parentCategoryName = SUBSTRING(parentCategoryName, 1, 191);
ALTER TABLE wcf1_user_group_option_category CHANGE parentCategoryName parentCategoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_menu_item SET menuItem = SUBSTRING(menuItem, 1, 191);
ALTER TABLE wcf1_user_menu_item CHANGE menuItem menuItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_menu_item SET parentMenuItem = SUBSTRING(parentMenuItem, 1, 191);
ALTER TABLE wcf1_user_menu_item CHANGE parentMenuItem parentMenuItem VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_notification_event SET eventName = SUBSTRING(eventName, 1, 191);
ALTER TABLE wcf1_user_notification_event CHANGE eventName eventName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_option SET optionName = SUBSTRING(optionName, 1, 191);
ALTER TABLE wcf1_user_option CHANGE optionName optionName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_option SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_user_option CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_option_category SET categoryName = SUBSTRING(categoryName, 1, 191);
ALTER TABLE wcf1_user_option_category CHANGE categoryName categoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_option_category SET parentCategoryName = SUBSTRING(parentCategoryName, 1, 191);
ALTER TABLE wcf1_user_option_category CHANGE parentCategoryName parentCategoryName VARCHAR(191) NOT NULL DEFAULT '';
UPDATE wcf1_user_profile_menu_item SET menuItem = SUBSTRING(menuItem, 1, 191);
ALTER TABLE wcf1_user_profile_menu_item CHANGE menuItem	menuItem VARCHAR(191) NOT NULL;
DATA;

$lines = explode("\n", StringUtil::trim($data));
if ($lines % 2 !== 0) {
	throw new SystemException("Query data must always come in pairs.");
}

$rebuildData = WCF::getSession()->getVar('__wcfUpdateRebuildTables');
if ($rebuildData === null) {
	$rebuildData = [
		'i' => 0,
		'max' => $lines / 2
	];
}

$i = $rebuildData['i'];

// truncate values
$statement = WCF::getDB()->prepareStatement($lines[$i * 2]);
$statement->execute();

// decrease column width
$statement = WCF::getDB()->prepareStatement($lines[$i * 2 + 1]);
$statement->execute();

$rebuildData['i']++;

if ($rebuildData['i'] === $rebuildData['max']) {
	WCF::getSession()->unregister('__wcfUpdateRebuildTables');
}
else {
	WCF::getSession()->register('__wcfUpdateRebuildTables', $rebuildData);
	
	// call this script again
	throw new SplitNodeException();
}
