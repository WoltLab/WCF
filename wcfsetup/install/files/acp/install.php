<?php
namespace wcf\acp;
use wcf\data\language\LanguageEditor;
use wcf\system\cache\CacheHandler;
use wcf\system\session\SessionHandler;
use wcf\system\template\ACPTemplateEngine;
use wcf\system\WCF;

/**
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category 	Community Framework
 */
// change the priority of the PIP's to "1"
$sql = "UPDATE	wcf".WCF_N."_package_installation_plugin
	SET	priority = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// change group options from admin group to true
$sql = "UPDATE	wcf".WCF_N."_user_group_option_value
	SET	optionValue = ?
	WHERE	groupID = ?
		AND optionValue = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1, 4, '0'));

// update accessible groups
$sql = "UPDATE	wcf".WCF_N."_user_group_option_value
	SET	optionValue = ?
	WHERE	groupID = ?
		AND optionValue = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array('1,2,3,4,5,6', 4, ''));

// reset sessions
SessionHandler::resetSessions();

// update acp session
$sql = "UPDATE	wcf".WCF_N."_acp_session
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// update acp templates
$sql = "UPDATE	wcf".WCF_N."_acp_template
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// update language
$sql = "UPDATE	wcf".WCF_N."_language_item
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

$sql = "UPDATE	wcf".WCF_N."_language_to_package
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// update installation logs
$sql = "UPDATE	wcf".WCF_N."_package_installation_file_log
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

$sql = "UPDATE	wcf".WCF_N."_package_installation_sql_log
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// update pips
$sql = "UPDATE	wcf".WCF_N."_package_installation_plugin
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// group options
$sql = "UPDATE	wcf".WCF_N."_user_group_option
	SET	packageID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array(1));

// reset all caches
CacheHandler::getInstance()->clear(WCF_DIR.'cache/', '*');

// delete language files
LanguageEditor::deleteLanguageFiles();

// delete all compiled templates
ACPTemplateEngine::deleteCompiledTemplates(WCF_DIR.'acp/templates/compiled/');
